<?php

namespace App\Services;

use App\DTOs\DocumentPackDTO;
use App\Enums\ApplicationType;
use App\Exceptions\DocumentGenerationException;
use App\Models\Address;
use App\Models\InmigrationFile;
use App\Models\User;
use App\Repositories\Contracts\EmployerRepository;
use App\Repositories\Contracts\FileRequirementRepository;
use App\Repositories\Contracts\ForeignerRepository;
use App\Repositories\Contracts\InmigrationFileRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio para la generación del pack de documentos de extranjería
 * Genera: Modelo EX (PDF), Contrato (PDF/DOCX) y Memoria Justificativa (PDF)
 */
class DocumentGenerationService
{
    /**
     * Directorio base para las plantillas
     */
    private const TEMPLATES_PATH = 'pdf';

    /**
     * Directorio de salida para los documentos generados
     */
    private const OUTPUT_PATH = 'generated_documents';

    public function __construct(
        protected InmigrationFileRepository $inmigrationFileRepository,
        protected FileRequirementRepository $fileRequirementRepository,
        protected EmployerRepository $employerRepository,
        protected ForeignerRepository $foreignerRepository,
        protected PdfGeneratorService $pdfGenerator,
    ) {}

    /**
     * Genera el pack completo de documentos para un expediente
     *
     * @param  int  $inmigrationFileId  ID del expediente
     * @param  User  $representative  Usuario autenticado (Representante Legal)
     * @return array Rutas de los documentos generados
     *
     * @throws DocumentGenerationException Si hay requisitos obligatorios incompletos
     */
    public function generateDocumentPack(int $inmigrationFileId, User $representative): array
    {
        // 1. Obtener el expediente con todas sus relaciones
        $inmigrationFile = $this->inmigrationFileRepository->findByIdWithRelations($inmigrationFileId);

        if (! $inmigrationFile) {
            throw DocumentGenerationException::fileNotFound($inmigrationFileId);
        }

        // 2. Validar requisitos obligatorios
        $this->validateMandatoryRequirements($inmigrationFileId);

        // 3. Recopilar todos los datos necesarios
        $documentPack = $this->buildDocumentPackDTO($inmigrationFile, $representative);

        // 4. Generar los documentos
        $generatedPaths = [];

        try {
            // Generar Modelo EX
            $generatedPaths['modelo_ex'] = $this->generateModeloEX($documentPack);

            // Generar Contrato
            $generatedPaths['contrato'] = $this->generateContrato($documentPack);

            // Generar Memoria Justificativa
            $generatedPaths['memoria'] = $this->generateMemoria($documentPack);
        } catch (\Exception $e) {
            // Limpiar archivos generados en caso de error
            $this->cleanupGeneratedFiles($generatedPaths);
            throw DocumentGenerationException::generationFailed($e->getMessage());
        }

        return [
            'success' => true,
            'files' => $generatedPaths,
            'file_code' => $inmigrationFile->file_code,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Valida que todos los requisitos obligatorios estén completados
     *
     * @throws DocumentGenerationException Si hay requisitos incompletos
     */
    public function validateMandatoryRequirements(int $inmigrationFileId): void
    {
        $incompleteRequirements = $this->fileRequirementRepository
            ->getIncompleteMandatory($inmigrationFileId);

        if ($incompleteRequirements->isNotEmpty()) {
            $requirementNames = $incompleteRequirements->pluck('name')->toArray();
            throw DocumentGenerationException::mandatoryRequirementsIncomplete($requirementNames);
        }
    }

    /**
     * Verifica si se puede generar la documentación (sin lanzar excepción)
     */
    public function canGenerateDocuments(int $inmigrationFileId): array
    {
        $incompleteRequirements = $this->fileRequirementRepository
            ->getIncompleteMandatory($inmigrationFileId);

        return [
            'can_generate' => $incompleteRequirements->isEmpty(),
            'pending_requirements' => $incompleteRequirements->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'target_entity' => $r->target_entity?->label(),
                'due_date' => $r->due_date?->format('d/m/Y'),
            ])->toArray(),
        ];
    }

    /**
     * Verifica la disponibilidad de datos para generar documentos
     */
    public function checkAvailability(InmigrationFile $file): array
    {
        return [
            'employer' => $file->employer_id !== null && $file->employer !== null,
            'foreigner' => $file->foreigner_id !== null && $file->foreigner !== null,
            'work_address' => $file->workAddress !== null,
            'employer_address' => $file->employer?->address !== null,
            'foreigner_address' => $file->foreigner?->address !== null,
            'job_title' => !empty($file->job_title),
            'dates' => $file->start_date !== null,
        ];
    }

    /**
     * Construye el DTO con todos los datos para la generación de documentos
     */
    protected function buildDocumentPackDTO(
        InmigrationFile $inmigrationFile,
        User $representative
    ): DocumentPackDTO {
        $employer = $inmigrationFile->employer;
        $foreigner = $inmigrationFile->foreigner;

        // Obtener direcciones
        $employerAddress = $this->getAddressData($employer);
        $foreignerAddress = $this->getAddressData($foreigner);
        $workAddress = $inmigrationFile->workAddress
            ? $this->getAddressArrayFromModel($inmigrationFile->workAddress)
            : null;

        return new DocumentPackDTO(
            inmigrationFile: $inmigrationFile,
            foreigner: $foreigner,
            employer: $employer,
            representative: $representative,
            employerAddress: $employerAddress,
            foreignerAddress: $foreignerAddress,
            workAddress: $workAddress,
        );
    }

    /**
     * Obtiene los datos de dirección de una entidad
     */
    protected function getAddressData($entity): array
    {
        $address = Address::where('addressable_type', get_class($entity))
            ->where('addressable_id', $entity->id)
            ->with(['municipality', 'province', 'country'])
            ->first();

        if (! $address) {
            return [];
        }

        return $this->getAddressArrayFromModel($address);
    }

    /**
     * Convierte un modelo Address a array
     */
    protected function getAddressArrayFromModel(Address $address): array
    {
        return [
            'street_name' => $address->street_name,
            'number' => $address->number,
            'floor_door' => $address->floor_door,
            'postal_code' => $address->postal_code,
            'municipality_name' => $address->municipality?->municipality_name,
            'province_name' => $address->province?->province_name,
            'country_name' => $address->country?->country_name,
        ];
    }

    /**
     * Genera el Modelo EX (formulario oficial de extranjería)
     */
    protected function generateModeloEX(DocumentPackDTO $pack): string
    {
        $applicationType = $pack->inmigrationFile->application_type;
        $templateName = $applicationType->templateName();

        if (empty($templateName)) {
            throw DocumentGenerationException::templateNotFound(
                "Modelo EX para {$applicationType->value}"
            );
        }

        $templatePath = resource_path(self::TEMPLATES_PATH . '/' . $templateName);

        if (! file_exists($templatePath)) {
            throw DocumentGenerationException::templateNotFound($templateName);
        }

        // Generar nombre de archivo de salida
        $outputFileName = $this->generateOutputFileName(
            $pack->inmigrationFile->file_code,
            $applicationType->value,
            'pdf'
        );

        // Generar el PDF usando el servicio de PDF
        $outputPath = $this->pdfGenerator->generateModeloEX(
            $pack->toTemplateData(),
            $templateName,
            $outputFileName
        );

        // Guardar metadatos
        $this->saveDocumentMetadata($outputPath, $pack->toTemplateData());

        return $outputPath;
    }

    /**
     * Genera el Contrato de Trabajo
     */
    protected function generateContrato(DocumentPackDTO $pack): string
    {
        $outputFileName = $this->generateOutputFileName(
            $pack->inmigrationFile->file_code,
            'Contrato',
            'pdf'
        );

        // Generar contrato desde vista Blade
        $outputPath = $this->pdfGenerator->generateContrato(
            $pack->toTemplateData(),
            $outputFileName
        );

        // Guardar metadatos
        $this->saveDocumentMetadata($outputPath, $pack->toTemplateData());

        return $outputPath;
    }

    /**
     * Genera la Memoria Justificativa
     */
    protected function generateMemoria(DocumentPackDTO $pack): string
    {
        $outputFileName = $this->generateOutputFileName(
            $pack->inmigrationFile->file_code,
            'Memoria',
            'pdf'
        );

        // Generar memoria desde vista Blade
        $outputPath = $this->pdfGenerator->generateMemoria(
            $pack->toTemplateData(),
            $outputFileName
        );

        // Guardar metadatos
        $this->saveDocumentMetadata($outputPath, $pack->toTemplateData());

        return $outputPath;
    }

    /**
     * Guarda los metadatos del documento generado
     */
    protected function saveDocumentMetadata(string $filePath, array $data): void
    {
        $metadataPath = $filePath . '.meta.json';
        $metadata = [
            'generated_at' => now()->toIso8601String(),
            'file_code' => $data['expediente_codigo'] ?? '',
            'worker' => $data['nombre_completo'] ?? '',
            'employer' => $data['razon_social'] ?? '',
        ];

        Storage::put($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Genera el nombre del archivo de salida
     */
    protected function generateOutputFileName(
        string $fileCode,
        string $documentType,
        string $extension
    ): string {
        $timestamp = now()->format('Ymd_His');
        $safeFileCode = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fileCode);

        return "{$safeFileCode}_{$documentType}_{$timestamp}.{$extension}";
    }

    /**
     * Limpia los archivos generados en caso de error
     */
    protected function cleanupGeneratedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
            // También eliminar metadatos
            $metaPath = $path . '.meta.json';
            if (Storage::exists($metaPath)) {
                Storage::delete($metaPath);
            }
        }
    }

    /**
     * Obtiene la lista de documentos generados para un expediente
     */
    public function getGeneratedDocuments(string $fileCode): array
    {
        $pattern = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fileCode);
        $files = Storage::files(self::OUTPUT_PATH);

        return collect($files)
            ->filter(fn ($file) => str_contains($file, $pattern) && ! str_ends_with($file, '.meta.json'))
            ->map(fn ($file) => [
                'path' => $file,
                'name' => basename($file),
                'size' => Storage::size($file),
                'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Descarga un documento generado
     */
    public function downloadDocument(string $path): ?string
    {
        if (! Storage::exists($path)) {
            return null;
        }

        return Storage::path($path);
    }
}
