<?php

namespace App\Enums;

/**
 * Estados del expediente de extranjería
 * Representa el ciclo de vida del trámite
 */
enum ImmigrationFileStatus: string
{
    case BORRADOR = 'borrador';
    case PENDIENTE_REVISION = 'pendiente_revision';
    case LISTO = 'listo';
    case PRESENTADO = 'presentado';
    case REQUERIDO = 'requerido';
    case FAVORABLE = 'favorable';
    case DENEGADO = 'denegado';
    case ARCHIVADO = 'archivado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la descripción legible del estado
     */
    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::PENDIENTE_REVISION => 'Pendiente de Revisión',
            self::LISTO => 'Listo para Presentar',
            self::PRESENTADO => 'Presentado',
            self::REQUERIDO => 'Requerido (Subsanación)',
            self::FAVORABLE => 'Resolución Favorable',
            self::DENEGADO => 'Resolución Denegada',
            self::ARCHIVADO => 'Archivado',
        };
    }

    /**
     * Indica si el estado permite edición del expediente
     */
    public function isEditable(): bool
    {
        return match ($this) {
            self::BORRADOR, self::PENDIENTE_REVISION, self::REQUERIDO => true,
            default => false,
        };
    }

    /**
     * Indica si el estado es terminal (finalizado)
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::FAVORABLE, self::DENEGADO, self::ARCHIVADO => true,
            default => false,
        };
    }

    /**
     * Retorna los estados a los que se puede transicionar desde el actual
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::BORRADOR => [self::PENDIENTE_REVISION, self::ARCHIVADO],
            self::PENDIENTE_REVISION => [self::LISTO, self::BORRADOR, self::ARCHIVADO],
            self::LISTO => [self::PRESENTADO, self::PENDIENTE_REVISION, self::ARCHIVADO],
            self::PRESENTADO => [self::REQUERIDO, self::FAVORABLE, self::DENEGADO],
            self::REQUERIDO => [self::PRESENTADO, self::ARCHIVADO],
            self::FAVORABLE => [self::ARCHIVADO],
            self::DENEGADO => [self::ARCHIVADO],
            self::ARCHIVADO => [],
        };
    }

    /**
     * Verifica si se puede transicionar al estado indicado
     */
    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
