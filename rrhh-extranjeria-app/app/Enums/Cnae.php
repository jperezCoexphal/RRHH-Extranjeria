<?php

namespace App\Enums;

/**
 * Códigos CNAE para actividades agrícolas, ganaderas, forestales y pesqueras
 */
enum Cnae: string
{
    // Cultivos no perennes
    case CNAE_0111 = '0111';
    case CNAE_0112 = '0112';
    case CNAE_0113 = '0113';
    case CNAE_0114 = '0114';
    case CNAE_0115 = '0115';
    case CNAE_0116 = '0116';
    case CNAE_0119 = '0119';

    // Cultivos perennes
    case CNAE_0121 = '0121';
    case CNAE_0122 = '0122';
    case CNAE_0123 = '0123';
    case CNAE_0124 = '0124';
    case CNAE_0125 = '0125';
    case CNAE_0126 = '0126';
    case CNAE_0127 = '0127';
    case CNAE_0128 = '0128';
    case CNAE_0129 = '0129';

    // Propagación de plantas
    case CNAE_0130 = '0130';

    // Producción ganadera
    case CNAE_0141 = '0141';
    case CNAE_0142 = '0142';
    case CNAE_0143 = '0143';
    case CNAE_0144 = '0144';
    case CNAE_0145 = '0145';
    case CNAE_0146 = '0146';
    case CNAE_0147 = '0147';
    case CNAE_0149 = '0149';

    // Producción agrícola combinada
    case CNAE_0150 = '0150';

    // Actividades de apoyo
    case CNAE_0161 = '0161';
    case CNAE_0162 = '0162';
    case CNAE_0163 = '0163';
    case CNAE_0164 = '0164';

    // Caza
    case CNAE_0170 = '0170';

    // Silvicultura
    case CNAE_0210 = '0210';
    case CNAE_0220 = '0220';
    case CNAE_0230 = '0230';
    case CNAE_0240 = '0240';

    // Pesca y acuicultura
    case CNAE_0311 = '0311';
    case CNAE_0312 = '0312';
    case CNAE_0321 = '0321';
    case CNAE_0322 = '0322';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la descripción del código CNAE
     */
    public function label(): string
    {
        return match ($this) {
            self::CNAE_0111 => 'Cultivo de cereales (excepto arroz), leguminosas y semillas oleaginosas',
            self::CNAE_0112 => 'Cultivo de arroz',
            self::CNAE_0113 => 'Cultivo de hortalizas, raíces y tubérculos',
            self::CNAE_0114 => 'Cultivo de caña de azúcar',
            self::CNAE_0115 => 'Cultivo de tabaco',
            self::CNAE_0116 => 'Cultivo de plantas para fibras textiles',
            self::CNAE_0119 => 'Otros cultivos no perennes',
            self::CNAE_0121 => 'Cultivo de la vid',
            self::CNAE_0122 => 'Cultivo de frutos tropicales y subtropicales',
            self::CNAE_0123 => 'Cultivo de cítricos',
            self::CNAE_0124 => 'Cultivo de frutos con hueso y pepitas',
            self::CNAE_0125 => 'Cultivo de otros árboles y arbustos frutales y frutos secos',
            self::CNAE_0126 => 'Cultivo de frutos oleaginosos',
            self::CNAE_0127 => 'Cultivo de plantas para bebidas',
            self::CNAE_0128 => 'Cultivo de especias, plantas aromáticas, medicinales y farmacéuticas',
            self::CNAE_0129 => 'Otros cultivos perennes',
            self::CNAE_0130 => 'Propagación de plantas',
            self::CNAE_0141 => 'Explotación de ganado bovino para la producción de leche',
            self::CNAE_0142 => 'Explotación de otro ganado bovino y búfalos',
            self::CNAE_0143 => 'Explotación de caballos y otros equinos',
            self::CNAE_0144 => 'Explotación de camellos y otros camélidos',
            self::CNAE_0145 => 'Explotación de ganado ovino y caprino',
            self::CNAE_0146 => 'Explotación de ganado porcino',
            self::CNAE_0147 => 'Avicultura',
            self::CNAE_0149 => 'Otras explotaciones de ganado',
            self::CNAE_0150 => 'Producción agrícola combinada con la producción ganadera',
            self::CNAE_0161 => 'Actividades de apoyo a la agricultura',
            self::CNAE_0162 => 'Actividades de apoyo a la ganadería',
            self::CNAE_0163 => 'Actividades de preparación posterior a la cosecha',
            self::CNAE_0164 => 'Tratamiento de semillas para reproducción',
            self::CNAE_0170 => 'Caza, captura de animales y servicios relacionados con las mismas',
            self::CNAE_0210 => 'Silvicultura y otras actividades forestales',
            self::CNAE_0220 => 'Explotación de la madera',
            self::CNAE_0230 => 'Recolección de productos silvestres, excepto madera',
            self::CNAE_0240 => 'Servicios de apoyo a la silvicultura',
            self::CNAE_0311 => 'Pesca marina',
            self::CNAE_0312 => 'Pesca en agua dulce',
            self::CNAE_0321 => 'Acuicultura marina',
            self::CNAE_0322 => 'Acuicultura en agua dulce',
        };
    }

    /**
     * Retorna código + descripción formateado
     */
    public function fullLabel(): string
    {
        return $this->value . ' - ' . $this->label();
    }
}
