<?php

namespace App\Services;

use App\DTOs\DocumentPackDTO;
use App\Exceptions\DocumentGenerationException;
use App\Models\Address;
use App\Models\InmigrationFile;
use App\Models\User;
use App\Repositories\Contracts\InmigrationFileRepository;

/**
 * Servicio para la generación del pack de documentos de extranjería
 * Genera: Modelo EX (PDF), Contrato (PDF) y Memoria Justificativa (PDF)
 * Los documentos se generan en caliente y no se almacenan permanentemente
 */
class DocumentGenerationService
{
    public function __construct(
        protected InmigrationFileRepository $inmigrationFileRepository,
        protected PdfGeneratorService $pdfGenerator,
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
            // Generar Modelo EX
            $documents["{$fileCode}_{$applicationType}.pdf"] = $this->pdfGenerator->generateModeloEX($templateData);

            // Generar Contrato
            $documents["{$fileCode}_Contrato.pdf"] = $this->pdfGenerator->generateContrato($templateData);

            // Generar Memoria Justificativa
            $documents["{$fileCode}_Memoria.pdf"] = $this->pdfGenerator->generateMemoria($templateData);
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
        $templateData = $documentPack->toTemplateData();

        $fileCode = $this->sanitizeFileCode($inmigrationFile->file_code);
        $applicationType = $inmigrationFile->application_type?->value ?? 'EX';

        return [
            'file_code' => $inmigrationFile->file_code,
            'filename' => "{$fileCode}_{$applicationType}.pdf",
            'content' => $this->pdfGenerator->generateModeloEX($templateData),
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
     * Sanitiza el código de expediente para usar en nombres de archivo
     */
    protected function sanitizeFileCode(string $fileCode): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fileCode);
    }
}
