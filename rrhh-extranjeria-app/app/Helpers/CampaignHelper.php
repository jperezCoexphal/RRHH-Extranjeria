<?php

namespace App\Helpers;

use Carbon\Carbon;

class CampaignHelper
{
    /**
     * Calcula la campana actual basada en la fecha.
     * La campana va del 1 de septiembre al 31 de agosto del ano siguiente.
     * Formato: "2025-2026"
     */
    public static function current(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();

        // Si estamos entre septiembre y diciembre, la campana es ano_actual - ano_siguiente
        // Si estamos entre enero y agosto, la campana es ano_anterior - ano_actual
        if ($date->month >= 9) {
            $startYear = $date->year;
            $endYear = $date->year + 1;
        } else {
            $startYear = $date->year - 1;
            $endYear = $date->year;
        }

        return "{$startYear}-{$endYear}";
    }

    /**
     * Obtiene la campana anterior a la actual.
     */
    public static function previous(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();

        if ($date->month >= 9) {
            $startYear = $date->year - 1;
            $endYear = $date->year;
        } else {
            $startYear = $date->year - 2;
            $endYear = $date->year - 1;
        }

        return "{$startYear}-{$endYear}";
    }

    /**
     * Obtiene la campana siguiente a la actual.
     */
    public static function next(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();

        if ($date->month >= 9) {
            $startYear = $date->year + 1;
            $endYear = $date->year + 2;
        } else {
            $startYear = $date->year;
            $endYear = $date->year + 1;
        }

        return "{$startYear}-{$endYear}";
    }

    /**
     * Valida si un string tiene formato de campana valido (YYYY-YYYY).
     */
    public static function isValid(string $campaign): bool
    {
        if (!preg_match('/^(\d{4})-(\d{4})$/', $campaign, $matches)) {
            return false;
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        return $endYear === $startYear + 1;
    }

    /**
     * Formatea una campana al formato completo YYYY-YYYY.
     * Soporta formatos de entrada: "2025-2026", "25-26", "2025-26", "25-2026"
     */
    public static function format(string $campaign): string
    {
        // Si ya tiene formato completo YYYY-YYYY, devolverlo tal cual
        if (preg_match('/^(\d{4})-(\d{4})$/', $campaign)) {
            return $campaign;
        }

        // Formato YY-YY (ej: "25-26")
        if (preg_match('/^(\d{2})-(\d{2})$/', $campaign, $matches)) {
            $startYear = self::expandYear((int) $matches[1]);
            $endYear = self::expandYear((int) $matches[2]);
            return "{$startYear}-{$endYear}";
        }

        // Formato YYYY-YY (ej: "2025-26")
        if (preg_match('/^(\d{4})-(\d{2})$/', $campaign, $matches)) {
            $startYear = $matches[1];
            $endYear = self::expandYear((int) $matches[2]);
            return "{$startYear}-{$endYear}";
        }

        // Formato YY-YYYY (ej: "25-2026")
        if (preg_match('/^(\d{2})-(\d{4})$/', $campaign, $matches)) {
            $startYear = self::expandYear((int) $matches[1]);
            $endYear = $matches[2];
            return "{$startYear}-{$endYear}";
        }

        // Si no coincide con ningun formato, devolver el original
        return $campaign;
    }

    /**
     * Expande un ano de 2 digitos a 4 digitos.
     * Asume que anos >= 50 son 1900s y < 50 son 2000s.
     */
    private static function expandYear(int $year): int
    {
        if ($year >= 100) {
            return $year;
        }

        return $year >= 50 ? 1900 + $year : 2000 + $year;
    }

    /**
     * Genera una lista de campanas desde un ano inicial hasta la actual.
     */
    public static function range(int $startYear, ?int $endYear = null): array
    {
        $endYear = $endYear ?? (int) date('Y');
        $campaigns = [];

        for ($year = $endYear; $year >= $startYear; $year--) {
            $campaigns[] = "{$year}-" . ($year + 1);
        }

        return $campaigns;
    }
}
