<?php

namespace App\Enums;

enum Gender: string
{
    
    case H = 'Hombre';
    case M = 'Mujer';
    case X = 'No especificado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}