<?php

namespace App\DTOs;

use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\InmigrationFile;
use App\Models\User;

/**
 * DTO que contiene todos los datos necesarios para generar el pack de documentos
 * Agrupa la información del expediente, trabajador, empleador y representante
 */
class DocumentPackDTO
{
    public function __construct(
        public readonly InmigrationFile $inmigrationFile,
        public readonly Foreigner $foreigner,
        public readonly Employer $employer,
        public readonly User $representative,
        public readonly array $employerAddress,
        public readonly array $foreignerAddress,
        public readonly ?array $workAddress = null,
    ) {}

    /**
     * Retorna los datos del trabajador formateados para las plantillas
     */
    public function getWorkerData(): array
    {
        $foreigner = $this->foreigner;
        $extraData = $foreigner->extraData;

        // Campos derivados de fecha de nacimiento
        $birthdate = $foreigner->birthdate;

        return [
            'nombre_completo' => "{$foreigner->first_name} {$foreigner->last_name}",
            'nombre' => $foreigner->first_name ?? '',
            'apellidos' => $foreigner->last_name ?? '',
            'primer_apellido' => $foreigner->last_name ? explode(' ', $foreigner->last_name, 2)[0] : '',
            'segundo_apellido' => $foreigner->last_name && str_contains($foreigner->last_name, ' ') ? explode(' ', $foreigner->last_name, 2)[1] : '',
            'pasaporte' => $foreigner->passport ?? '',
            'nie' => $foreigner->nie ?? '',
            'nie_letra_inicial' => $foreigner->nie ? mb_substr($foreigner->nie, 0, 1) : '',
            'nie_numero' => $foreigner->nie ? mb_substr($foreigner->nie, 1, -1) : '',
            'nie_letra_final' => $foreigner->nie ? mb_substr($foreigner->nie, -1) : '',
            'niss' => $foreigner->niss ?? '',
            'sexo' => $foreigner->gender?->value ?? '',
            'fecha_nacimiento' => $birthdate?->format('d/m/Y') ?? '',
            'fecha_nacimiento_dia' => $birthdate?->format('d') ?? '',
            'fecha_nacimiento_mes' => $birthdate?->format('m') ?? '',
            'fecha_nacimiento_anio' => $birthdate?->format('Y') ?? '',
            'estado_civil' => $foreigner->marital_status?->value ?? '',
            'nacionalidad' => $foreigner->nationality?->country_name ?? '',
            'pais_nacimiento' => $foreigner->birthCountry?->country_name ?? '',
            'lugar_nacimiento' => $foreigner->birthplace_name ?? '',
            'nombre_padre' => $extraData?->father_name ?? '',
            'nombre_madre' => $extraData?->mother_name ?? '',
            'telefono' => $extraData?->phone ?? '',
            'email' => $extraData?->email ?? '',
            'tutor_nombre' => $extraData?->legal_guardian_name ?? '',
            'tutor_documento' => $extraData?->legal_guardian_identity_number ?? '',
            'tutor_titulo' => $extraData?->guardianship_title ?? '',
            'trabajador_direccion' => $this->formatAddress($this->foreignerAddress),
            'trabajador_direccion_calle' => $this->foreignerAddress['street_name'] ?? '',
            'trabajador_direccion_numero' => $this->foreignerAddress['number'] ?? '',
            'trabajador_direccion_piso_puerta' => $this->foreignerAddress['floor_door'] ?? '',
            'trabajador_direccion_codigo_postal' => $this->foreignerAddress['postal_code'] ?? '',
            'trabajador_direccion_municipio' => $this->foreignerAddress['municipality_name'] ?? '',
            'trabajador_direccion_provincia' => $this->foreignerAddress['province_name'] ?? '',
        ];
    }

    /**
     * Retorna los datos del empleador formateados para las plantillas
     */
    public function getEmployerData(): array
    {
        $employer = $this->employer;
        $isFreelancer = in_array($employer->legal_form->name, ['EI', 'ERL']);

        $data = [
            'razon_social' => $employer->fiscal_name,
            'nombre_comercial' => $employer->comercial_name ?? $employer->fiscal_name,
            'nif' => $employer->nif,
            'ccc' => $employer->ccc,
            'cnae' => $employer->cnae,
            'forma_juridica' => $employer->legal_form->value,
            'email' => $employer->email ?? '',
            'telefono' => $employer->phone ?? '',
            'empleador_direccion' => $this->formatAddress($this->employerAddress),
            'empleador_direccion_calle' => $this->employerAddress['street_name'] ?? '',
            'empleador_direccion_numero' => $this->employerAddress['number'] ?? '',
            'empleador_direccion_piso_puerta' => $this->employerAddress['floor_door'] ?? '',
            'empleador_direccion_codigo_postal' => $this->employerAddress['postal_code'] ?? '',
            'empleador_direccion_municipio' => $this->employerAddress['municipality_name'] ?? '',
            'empleador_direccion_provincia' => $this->employerAddress['province_name'] ?? '',
        ];

        if ($isFreelancer && $employer->freelancer) {
            $data['representante_nombre'] = "{$employer->freelancer->first_name} {$employer->freelancer->last_name}";
            $data['representante_documento'] = $employer->nif;
            $data['representante_cargo'] = 'Empresario Individual';
        } elseif ($employer->company) {
            $data['representante_nombre'] = $employer->company->representative_name;
            $data['representante_documento'] = $employer->company->representative_identity_number;
            $data['representante_cargo'] = $employer->company->representative_title;
        }

        return $data;
    }

    /**
     * Retorna los datos laborales formateados para las plantillas
     */
    public function getJobData(): array
    {
        $file = $this->inmigrationFile;

        // Campos derivados de fechas
        $startDate = $file->start_date;
        $endDate = $file->end_date;

        return [
            'expediente_codigo' => $file->file_code ?? '',
            'expediente_titulo' => $file->file_title ?? '',
            'campaña' => $file->campaign ?? '',
            'tipo_solicitud' => $file->application_type?->value ?? '',
            'puesto_trabajo' => $file->job_title ?? '',
            'fecha_inicio' => $startDate?->format('d/m/Y') ?? '',
            'fecha_inicio_dia' => $startDate?->format('d') ?? '',
            'fecha_inicio_mes' => $startDate?->format('m') ?? '',
            'fecha_inicio_anio' => $startDate?->format('Y') ?? '',
            'fecha_fin' => $endDate?->format('d/m/Y') ?? '',
            'fecha_fin_dia' => $endDate?->format('d') ?? '',
            'fecha_fin_mes' => $endDate?->format('m') ?? '',
            'fecha_fin_anio' => $endDate?->format('Y') ?? '',
            'salario' => $file->salary ? number_format($file->salary, 2, ',', '.') . ' EUR' : '',
            'salario_numero' => $file->salary ? number_format($file->salary, 2, ',', '.') : '',
            'tipo_jornada' => $file->working_day_type?->label() ?? '',
            'horas_semanales' => $file->working_hours ?? '',
            'periodo_prueba' => $file->probation_period ? "{$file->probation_period} dias" : '',
            'periodo_prueba_dias' => $file->probation_period ?? '',
            'direccion_trabajo' => $this->workAddress ? $this->formatAddress($this->workAddress) : '',
            'direccion_trabajo_calle' => $this->workAddress['street_name'] ?? '',
            'direccion_trabajo_numero' => $this->workAddress['number'] ?? '',
            'direccion_trabajo_piso_puerta' => $this->workAddress['floor_door'] ?? '',
            'direccion_trabajo_codigo_postal' => $this->workAddress['postal_code'] ?? '',
            'direccion_trabajo_municipio' => $this->workAddress['municipality_name'] ?? '',
            'direccion_trabajo_provincia' => $this->workAddress['province_name'] ?? '',
        ];
    }

    /**
     * Retorna los datos del representante legal (usuario autenticado)
     */
    public function getRepresentativeData(): array
    {
        return [
            'gestor_nombre' => "{$this->representative->first_name} {$this->representative->last_name}",
            'gestor_email' => $this->representative->email,
            'fecha_generacion' => now()->format('d/m/Y'),
            'hora_generacion' => now()->format('H:i'),
        ];
    }

    /**
     * Retorna todos los datos combinados para usar en las plantillas
     */
    public function toTemplateData(): array
    {
        return array_merge(
            $this->getWorkerData(),
            $this->getEmployerData(),
            $this->getJobData(),
            $this->getRepresentativeData(),
        );
    }

    /**
     * Formatea una dirección como string
     */
    private function formatAddress(array $address): string
    {
        $parts = array_filter([
            $address['street_name'] ?? null,
            isset($address['number']) ? "nº {$address['number']}" : null,
            $address['floor_door'] ?? null,
            $address['postal_code'] ?? null,
            $address['municipality_name'] ?? null,
            $address['province_name'] ?? null,
        ]);

        return implode(', ', $parts);
    }
}
