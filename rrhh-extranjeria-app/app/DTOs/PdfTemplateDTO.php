<?php

namespace App\DTOs;

use App\Enums\ApplicationType;
use App\Enums\PdfTemplateType;
use Illuminate\Http\UploadedFile;

/**
 * DTO para crear o actualizar plantillas PDF
 */
class PdfTemplateDTO
{
    public function __construct(
        public readonly string $name,
        public readonly PdfTemplateType $documentType,
        public readonly ?string $description = null,
        public readonly ?ApplicationType $applicationType = null,
        public readonly ?UploadedFile $file = null,
        public readonly ?string $originalFilename = null,
        public readonly ?string $storagePath = null,
        public readonly bool $isDefault = false,
        public readonly bool $isActive = true,
        public readonly ?array $fieldMapping = null,
        public readonly ?array $extractedFields = null,
        public readonly int $totalFields = 0,
        public readonly ?int $createdBy = null,
    ) {}

    /**
     * Crea un DTO desde un array de datos (típicamente de un formulario)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            documentType: $data['document_type'] instanceof PdfTemplateType
                ? $data['document_type']
                : PdfTemplateType::from($data['document_type']),
            description: $data['description'] ?? null,
            applicationType: isset($data['application_type']) && $data['application_type']
                ? ($data['application_type'] instanceof ApplicationType
                    ? $data['application_type']
                    : ApplicationType::from($data['application_type']))
                : null,
            file: $data['file'] ?? null,
            originalFilename: $data['original_filename'] ?? null,
            storagePath: $data['storage_path'] ?? null,
            isDefault: $data['is_default'] ?? false,
            isActive: $data['is_active'] ?? true,
            fieldMapping: $data['field_mapping'] ?? null,
            extractedFields: $data['extracted_fields'] ?? null,
            totalFields: $data['total_fields'] ?? 0,
            createdBy: $data['created_by'] ?? null,
        );
    }

    /**
     * Convierte el DTO a un array para persistir en la base de datos
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'document_type' => $this->documentType->value,
            'application_type' => $this->applicationType?->value,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive,
        ];

        if ($this->originalFilename !== null) {
            $data['original_filename'] = $this->originalFilename;
        }

        if ($this->storagePath !== null) {
            $data['storage_path'] = $this->storagePath;
        }

        if ($this->fieldMapping !== null) {
            $data['field_mapping'] = $this->fieldMapping;
        }

        if ($this->extractedFields !== null) {
            $data['extracted_fields'] = $this->extractedFields;
        }

        if ($this->totalFields > 0) {
            $data['total_fields'] = $this->totalFields;
        }

        if ($this->createdBy !== null) {
            $data['created_by'] = $this->createdBy;
        }

        return $data;
    }
}
