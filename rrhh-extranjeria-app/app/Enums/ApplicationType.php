<?php

namespace App\Enums;

/**
 * Tipos de solicitud/trámite de extranjería
 * Corresponde a los modelos oficiales EX del Ministerio del Interior
 */
enum ApplicationType: string
{
    case EX_00 = 'EX-00';
    case EX_01 = 'EX-01';
    case EX_02 = 'EX-02';
    case EX_03 = 'EX-03';
    case EX_04 = 'EX-04';
    case EX_05 = 'EX-05';
    case EX_06 = 'EX-06';
    case EX_07 = 'EX-07';
    case EX_08 = 'EX-08';
    case EX_09 = 'EX-09';
    case EX_10 = 'EX-10';
    case EX_11 = 'EX-11';
    case EX_12 = 'EX-12';
    case EX_13 = 'EX-13';
    case EX_14 = 'EX-14';
    case EX_15 = 'EX-15';
    case EX_16 = 'EX-16';
    case EX_17 = 'EX-17';
    case EX_18 = 'EX-18';
    case EX_19 = 'EX-19';
    case EX_20 = 'EX-20';
    case EX_21 = 'EX-21';
    case EX_22 = 'EX-22';
    case EX_23 = 'EX-23';
    case EX_24 = 'EX-24';
    case EX_25 = 'EX-25';
    case EX_26 = 'EX-26';
    case EX_27 = 'EX-27';
    case EX_28 = 'EX-28';
    case EX_29 = 'EX-29';
    case EX_30 = 'EX-30';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna la descripción oficial del tipo de solicitud
     */
    public function description(): string
    {
        return match ($this) {
            self::EX_00 => 'Solicitud de autorización de residencia temporal y trabajo por cuenta propia',
            self::EX_01 => 'Solicitud de autorización de estancia por estudios, movilidad de alumnos, prácticas no laborales o servicios de voluntariado',
            self::EX_02 => 'Solicitud de autorización de residencia temporal no lucrativa',
            self::EX_03 => 'Solicitud de autorización de residencia temporal y trabajo por cuenta ajena',
            self::EX_04 => 'Solicitud de autorización de residencia temporal y trabajo por cuenta ajena de duración determinada',
            self::EX_05 => 'Solicitud de autorización de residencia temporal y trabajo de profesionales altamente cualificados',
            self::EX_06 => 'Solicitud de autorización de residencia temporal por reagrupación familiar',
            self::EX_07 => 'Solicitud de autorización de residencia de larga duración o de larga duración-UE',
            self::EX_08 => 'Solicitud de autorización de residencia y trabajo independiente',
            self::EX_09 => 'Solicitud de tarjeta de identidad de extranjero',
            self::EX_10 => 'Solicitud de autorización de residencia por circunstancias excepcionales',
            self::EX_11 => 'Solicitud de autorización de residencia temporal y trabajo en el marco de prestaciones transnacionales de servicios',
            self::EX_12 => 'Solicitud de prórroga de estancia de corta duración',
            self::EX_13 => 'Solicitud de autorización de regreso',
            self::EX_14 => 'Solicitud de cédula de inscripción o documento de viaje',
            self::EX_15 => 'Solicitud de tarjeta de familiar de ciudadano de la Unión',
            self::EX_16 => 'Solicitud de certificado de registro de ciudadano de la Unión',
            self::EX_17 => 'Solicitud de tarjeta de residencia de familiar de ciudadano de la Unión',
            self::EX_18 => 'Solicitud de certificado de residencia permanente de ciudadano de la Unión',
            self::EX_19 => 'Solicitud de tarjeta de residencia permanente de familiar de ciudadano de la Unión',
            self::EX_20 => 'Solicitud de autorización de trabajo por cuenta ajena (notificación empresario)',
            self::EX_21 => 'Solicitud colectiva de autorización de residencia temporal y trabajo',
            self::EX_22 => 'Solicitud de renovación de autorización de residencia temporal y trabajo',
            self::EX_23 => 'Solicitud de modificación de autorización de residencia y trabajo',
            self::EX_24 => 'Solicitud de autorización de residencia y trabajo de investigadores',
            self::EX_25 => 'Solicitud de visado',
            self::EX_26 => 'Solicitud de modificación de autorización de residencia o estancia',
            self::EX_27 => 'Solicitud de informe de arraigo',
            self::EX_28 => 'Solicitud de autorización de residencia por traslado intraempresarial',
            self::EX_29 => 'Solicitud de autorización de residencia para prácticas',
            self::EX_30 => 'Solicitud de autorización de residencia para búsqueda de empleo o emprendimiento',
        };
    }

    /**
     * Retorna el nombre del formulario PDF asociado
     */
    public function templateName(): string
    {
        return match ($this) {
            self::EX_03 => 'EX03. Formulario autorización de residencia temporal y trabajo por cuenta ajena o autorización de trabajo por cuenta ajena. Editable.pdf',
            self::EX_10 => 'EX10. Formulario autorización de residencia por circunstancias excepcionales. Editable.pdf',
            self::EX_26 => 'EX26. Formulario modificación de autorización de residencia o estancia. Editable.pdf',
            default => '',
        };
    }
}
