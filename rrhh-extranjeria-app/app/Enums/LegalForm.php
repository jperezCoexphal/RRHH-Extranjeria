<?php

namespace App\Enums;

enum LegalForm: string
{
    
    case AIE = 'Agrupacion de Interés Economico';
    case CB = 'Comunidad de Bienes';
    case ERL = 'Emprendedor de Responsabilidad Limitada';
    case EI = 'Empresario Individual (Autónomo)';
    case SA = 'Sociedad Anónima';
    case SAL = 'Sociedad Anónima Laboral';
    case SAT = 'Sociedad Agraria de Transformación';
    case SCP = 'Sociedad Civil Privada';
    case SC = 'Sociedad Colectiva';
    case SCA = 'Sociedad Comanditaria por Acciones';
    case COOP = 'Sociedad Cooperativa';
    case SCS = 'Sociedad Comanditaria Simple';
    case ECR = 'Entidades Capital-Riesgo';
    case SCTA  = 'Sociedad Cooperativa de Trabajo Asociado';
    case SGR = 'Sociedad de Garantía Recíproca';
    case SRLL = 'Sociedad de Responsabilidad Limitada Laboral';
    case SP = 'Sociedades Profesionales';
    case SL = 'Sociedad Limitada';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function type (): string
    {
        return match($this) {
            self::EI, self::ERL => 'PHYSICAL',
            default => 'LEGAL'
        };
    }
}