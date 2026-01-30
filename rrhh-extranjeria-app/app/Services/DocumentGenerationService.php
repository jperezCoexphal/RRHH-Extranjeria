<?php

namespace App\Services;

use App\DTOs\DocumentPackDTO;
use App\Enums\PdfTemplateType;
use App\Exceptions\DocumentGenerationException;
use App\Models\Address;
use App\Models\InmigrationFile;
use App\Models\User;
use App\Repositories\Contracts\InmigrationFileRepository;
use App\Repositories\Contracts\PdfTemplateRepository;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para la generación del pack de documentos de extranjería
 * Genera: Modelo EX (PDF), Contrato (PDF) y Memoria Justificativa (PDF)
 * Los documentos se generan en caliente y no se almacenan permanentemente
 *
 * Prioridad de generación:
 * 1. Plantilla PDF AcroForm con mapeo configurado
 * 2. Fallback a generación desde vista Blade (DomPDF)
 */
class DocumentGenerationService
{
    public function __construct(
        protected InmigrationFileRepository $inmigrationFileRepository,
        protected PdfGeneratorService $pdfGenerator,
        protected PdfTemplateRepository $pdfTemplateRepository,
        protected PdfTemplateFillerService $pdfFiller,
    ) {}

    /**
     * Genera el pack completo de documentos para un expediente
     * Devuelve un array con el contenido binario de cada PDF
     *
     * @param  int  $inmigrationFileId  ID del expediente
     * @param  User  $representative  Usuario autenticado (Representante Legal)
     * @return array Array con ['file_code' => string, 'documents' => [name => content]]
     *
     * @throws DocumentGenerationException Si faltan datos necesarios
     */
    public function generateDocumentPack(int $inmigrationFileId, User $representative): array
    {
        // 1. Obtener el expediente con todas sus relaciones
        $inmigrationFile = $this->inmigrationFileRepository->findByIdWithRelations($inmigrationFileId);

        if (! $inmigrationFile) {
            throw DocumentGenerationException::fileNotFound($inmigrationFileId);
        }

        // 2. Validar que existan los datos necesarios
        if (! $inmigrationFile->employer) {
            throw DocumentGenerationException::generationFailed('El expediente no tiene empleador asignado.');
        }

        if (! $inmigrationFile->foreigner) {
            throw DocumentGenerationException::generationFailed('El expediente no tiene trabajador extranjero asignado.');
        }

        // 3. Recopilar todos los datos necesarios
        $documentPack = $this->buildDocumentPackDTO($inmigrationFile, $representative);
        $templateData = $documentPack->toTemplateData();

        // 4. Generar los documentos en memoria
        $fileCode = $this->sanitizeFileCode($inmigrationFile->file_code);
        $applicationType = $inmigrationFile->application_type?->value ?? 'EX';

        $documents = [];

        try {
            // Generar Modelo EX (prioridad: plantilla PDF, fallback: Blade)
            $documents["{$fileCode}_{$applicationType}.pdf"] = $this->generateDocument(
                PdfTemplateType::MODELO_EX,
                $documentPack,
                fn($data) => $this->pdfGenerator->generateModeloEX($data)
            );

            // Generar Contrato (prioridad: plantilla PDF, fallback: Blade)
            $documents["{$fileCode}_Contrato.pdf"] = $this->generateDocument(
                PdfTemplateType::CONTRATO,
                $documentPack,
                fn($data) => $this->pdfGenerator->generateContrato($data)
            );

            // Generar Memoria Justificativa (prioridad: plantilla PDF, fallback: Blade)
            $documents["{$fileCode}_Memoria.pdf"] = $this->generateDocument(
                PdfTemplateType::MEMORIA,
                $documentPack,
                fn($data) => $this->pdfGenerator->generateMemoria($data)
            );
        } catch (\Exception $e) {
            throw DocumentGenerationException::generationFailed($e->getMessage());
        }

        return [
            'file_code' => $inmigrationFile->file_code,
            'documents' => $documents,
        ];
    }

    /**
     * Genera solo el Modelo EX
     */
    public function generateModeloEX(int $inmigrationFileId, User $representative): array
    {
        $inmigrationFile = $this->inmigrationFileRepository->findByIdWithRelations($inmigrationFileId);

        

        if (! $inmigrationFile) {
            throw DocumentGenerationException::fileNotFound($inmigrationFileId);
        }

        if (! $inmigrationFile->employer || ! $inmigrationFile->foreigner) {
            throw DocumentGenerationException::generationFailed('Faltan datos del empleador o trabajador.');
        }

        $documentPack = $this->buildDocumentPackDTO($inmigrationFile, $representative);

        
        $fileCode = $this->sanitizeFileCode($inmigrationFile->file_code);
        $applicationType = $inmigrationFile->application_type?->value ?? 'EX';

        
        return [
            'file_code' => $inmigrationFile->file_code,
            'filename' => "{$fileCode}_{$applicationType}.pdf",
            'content' => $this->generateDocument(
                PdfTemplateType::MODELO_EX,
                $documentPack,
                fn($data) => $this->pdfGenerator->generateModeloEX($data)
            ),
        ];
    }

    /**
     * Genera un documento usando plantilla PDF si está disponible, o fallback a Blade
     *
     * @param PdfTemplateType $documentType Tipo de documento a generar
     * @param DocumentPackDTO $documentPack Datos del expediente
     * @param callable $fallbackGenerator Función de fallback que genera PDF desde Blade
     * @return string Contenido binario del PDF generado
     */
    protected function generateDocument(
        PdfTemplateType $documentType,
        DocumentPackDTO $documentPack,
        callable $fallbackGenerator
    ): string {
        // Buscar plantilla predeterminada para el tipo de documento
        $applicationType = $documentPack->inmigrationFile->application_type;
        $template = $this->pdfTemplateRepository->getDefaultTemplate($documentType, $applicationType);

        

        // Si hay plantilla activa con archivo, usar el filler (con o sin mapeo)
        if ($template && $template->is_active && $template->file_exists) {
            try {
                Log::debug("Generando {$documentType->value} con plantilla PDF #{$template->id}");
                return $this->pdfFiller->fill($template, $documentPack);
            } catch (\Exception $e) {
                // Si falla la plantilla, hacer fallback a Blade
                Log::warning("Error al usar plantilla PDF #{$template->id}, usando fallback Blade", [
                    'error' => $e->getMessage(),
                    'document_type' => $documentType->value,
                ]);
            }
        }

        // Fallback a generación desde Blade
        Log::debug("Generando {$documentType->value} con Blade (sin plantilla PDF)");
        return $fallbackGenerator($documentPack->toTemplateData());
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
            'job_title' => ! empty($file->job_title),
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
     * Obtiene los datos de dirección de una entidad usando la relación polimórfica
     */
    protected function getAddressData($entity): array
    {
        // Usar la relación definida en el modelo (ya cargada con eager loading)
        $address = $entity->address;

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
     * Sanitiza el código de expediente para usar en nombres de archivo
     */
    protected function sanitizeFileCode(string $fileCode): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fileCode);
    }
}
