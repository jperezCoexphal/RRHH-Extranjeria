<?php

namespace App\Enums;

/**
 * Entidad objetivo para los requisitos del expediente
 * Indica a quién corresponde aportar la documentación
 */
enum TargetEntity: string
{
    case WORKER = 'WORKER';
    case EMPLOYER = 'EMPLOYER';
    case REPRESENTATIVE = 'REPRESENTATIVE';
    case GENERAL = 'GENERAL';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la descripción legible de la entidad
     */
    public function label(): string
    {
        return match ($this) {
            self::WORKER => 'Trabajador',
            self::EMPLOYER => 'Empleador',
            self::REPRESENTATIVE => 'Representante Legal',
            self::GENERAL => 'General',
        };
    }
}
