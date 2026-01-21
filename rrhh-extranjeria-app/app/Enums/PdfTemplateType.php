<?php

namespace App\Enums;

/**
 * Tipos de plantillas PDF para documentos de extranjería
 */
enum PdfTemplateType: string
{
    case MODELO_EX = 'MODELO_EX';
    case CONTRATO = 'CONTRATO';
    case MEMORIA = 'MEMORIA';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la etiqueta legible del tipo
     */
    public function label(): string
    {
        return match ($this) {
            self::MODELO_EX => 'Modelo EX',
            self::CONTRATO => 'Contrato',
            self::MEMORIA => 'Memoria Justificativa',
        };
    }

    /**
     * Retorna la descripción del tipo de documento
     */
    public function description(): string
    {
        return match ($this) {
            self::MODELO_EX => 'Formularios oficiales EX del Ministerio del Interior',
            self::CONTRATO => 'Contrato de trabajo para extranjeros',
            self::MEMORIA => 'Memoria justificativa de necesidad de contratación',
        };
    }

    /**
     * Retorna el icono Bootstrap asociado al tipo
     */
    public function icon(): string
    {
        return match ($this) {
            self::MODELO_EX => 'bi-file-earmark-text',
            self::CONTRATO => 'bi-file-earmark-richtext',
            self::MEMORIA => 'bi-file-earmark-medical',
        };
    }

    /**
     * Retorna el color de badge asociado al tipo
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::MODELO_EX => 'primary',
            self::CONTRATO => 'success',
            self::MEMORIA => 'info',
        };
    }
}
