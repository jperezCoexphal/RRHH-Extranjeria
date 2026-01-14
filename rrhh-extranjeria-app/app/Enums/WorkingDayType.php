<?php

namespace App\Enums;

/**
 * Tipo de jornada laboral
 */
enum WorkingDayType: string
{
    case COMPLETA = 'completa';
    case PARCIAL = 'parcial';
    case DISCONTINUO = 'discontinuo';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la descripción legible del tipo de jornada
     */
    public function label(): string
    {
        return match ($this) {
            self::COMPLETA => 'Jornada Completa',
            self::PARCIAL => 'Jornada Parcial',
            self::DISCONTINUO => 'Fijo Discontinuo',
        };
    }

    /**
     * Retorna las horas semanales por defecto según el tipo
     */
    public function defaultHours(): float
    {
        return match ($this) {
            self::COMPLETA => 40.0,
            self::PARCIAL => 20.0,
            self::DISCONTINUO => 40.0,
        };
    }
}
