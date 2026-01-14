<?php

namespace App\Enums;

enum MaritalStatus: string
{

    case Sol = 'Soltero/a';
    case Cas = 'Casado/a';
    case Viu = 'Viudo/a';
    case Div = 'Divorciado';
    case Sep = 'Separado/a';

    public static function values() : array
    {

        return array_column(self::cases(), 'value');

    }
}