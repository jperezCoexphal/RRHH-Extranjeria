<?php

namespace App\DTOs;

/**
 * DTO para campos extraídos de un PDF AcroForm
 */
class PdfFieldDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $value = null,
        public readonly array $options = [],
        public readonly bool $isRequired = false,
        public readonly ?int $maxLength = null,
    ) {}

    /**
     * Crea un DTO desde la salida de pdftk dump_data_fields
     */
    public static function fromPdftkOutput(array $fieldData): self
    {
        $name = $fieldData['FieldName'] ?? '';
        $type = self::normalizeFieldType($fieldData['FieldType'] ?? 'Text');
        $value = $fieldData['FieldValue'] ?? null;

        // Extraer opciones para campos de selección
        $options = [];
        if (isset($fieldData['FieldStateOption'])) {
            $options = is_array($fieldData['FieldStateOption'])
                ? $fieldData['FieldStateOption']
                : [$fieldData['FieldStateOption']];
        }

        // Determinar si es requerido (flag 2 en FieldFlags)
        $flags = (int) ($fieldData['FieldFlags'] ?? 0);
        $isRequired = ($flags & 2) === 2;

        // Extraer longitud máxima si existe
        $maxLength = isset($fieldData['FieldMaxLength'])
            ? (int) $fieldData['FieldMaxLength']
            : null;

        return new self(
            name: $name,
            type: $type,
            value: $value,
            options: $options,
            isRequired: $isRequired,
            maxLength: $maxLength,
        );
    }

    /**
     * Normaliza el tipo de campo de PDF a un tipo simplificado
     */
    private static function normalizeFieldType(string $pdfType): string
    {
        return match (strtolower($pdfType)) {
            'text' => 'text',
            'button' => 'checkbox',
            'choice' => 'select',
            'signature' => 'signature',
            default => 'text',
        };
    }

    /**
     * Convierte el DTO a array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'options' => $this->options,
            'is_required' => $this->isRequired,
            'max_length' => $this->maxLength,
        ];
    }

    /**
     * Crea un DTO desde un array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'] ?? 'text',
            value: $data['value'] ?? null,
            options: $data['options'] ?? [],
            isRequired: $data['is_required'] ?? false,
            maxLength: $data['max_length'] ?? null,
        );
    }
}
