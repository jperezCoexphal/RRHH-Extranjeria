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

        return [
            'nombre_completo' => "{$foreigner->first_name} {$foreigner->last_name}",
            'nombre' => $foreigner->first_name,
            'apellidos' => $foreigner->last_name,
            'pasaporte' => $foreigner->passport,
            'nie' => $foreigner->nie,
            'niss' => $foreigner->niss,
            'sexo' => $foreigner->gender->value,
            'fecha_nacimiento' => $foreigner->birthdate->format('d/m/Y'),
            'estado_civil' => $foreigner->marital_status->value,
            'nacionalidad' => $foreigner->nationality?->country_name ?? '',
            'pais_nacimiento' => $foreigner->birthCountry?->country_name ?? '',
            'lugar_nacimiento' => $foreigner->birthplace_name,
            'nombre_padre' => $extraData?->father_name ?? '',
            'nombre_madre' => $extraData?->mother_name ?? '',
            'telefono' => $extraData?->phone ?? '',
            'email' => $extraData?->email ?? '',
            'tutor_nombre' => $extraData?->legal_guardian_name ?? '',
            'tutor_documento' => $extraData?->legal_guardian_identity_number ?? '',
            'tutor_titulo' => $extraData?->guardianship_title ?? '',
            'direccion' => $this->formatAddress($this->foreignerAddress),
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
            'direccion' => $this->formatAddress($this->employerAddress),
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

        return [
            'expediente_codigo' => $file->file_code,
            'expediente_titulo' => $file->file_title,
            'campana' => $file->campaign,
            'tipo_solicitud' => $file->application_type->value,
            'puesto_trabajo' => $file->job_title,
            'fecha_inicio' => $file->start_date->format('d/m/Y'),
            'fecha_fin' => $file->end_date?->format('d/m/Y') ?? '',
            'salario' => $file->salary ? number_format($file->salary, 2, ',', '.') . ' €' : '',
            'tipo_jornada' => $file->working_day_type?->label() ?? '',
            'horas_semanales' => $file->working_hours ?? '',
            'periodo_prueba' => $file->probation_period ? "{$file->probation_period} días" : '',
            'direccion_trabajo' => $this->workAddress ? $this->formatAddress($this->workAddress) : '',
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
