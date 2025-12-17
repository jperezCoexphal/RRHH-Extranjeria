<?php

namespace App\Enums;

enum RelationType: string
{
    case Spouse = 'Cónyuge';
    case Partner = 'Pareja de Hecho';
    case Minors = 'Hijos Menores';
    case Adults = 'Hijos Mayores';
    case Ascendants = 'Padres/Ascendientes';
    case Extended = 'Familia Extensa';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}