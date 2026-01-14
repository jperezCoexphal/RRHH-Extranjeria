<?php

namespace App\Http\Requests\InmigrationFile;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\WorkingDayType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInmigrationFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign' => ['required', 'string', 'size:9'],
            'file_code' => ['required', 'string', 'max:12', 'unique:inmigration_files,file_code'],
            'file_title' => ['required', 'string', 'max:165'],
            'application_type' => ['required', Rule::enum(ApplicationType::class)],
            'status' => ['nullable', Rule::enum(ImmigrationFileStatus::class)],
            'job_title' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'working_day_type' => ['nullable', Rule::enum(WorkingDayType::class)],
            'working_hours' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'probation_period' => ['nullable', 'integer', 'min:0', 'max:365'],
            'employer_id' => ['required', 'integer', 'exists:employers,id'],
            'foreigner_id' => ['required', 'integer', 'exists:foreigners,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign.required' => 'La campaña es obligatoria.',
            'campaign.size' => 'La campaña debe tener exactamente 9 caracteres (ej: 2025-2026).',
            'file_code.required' => 'El código de expediente es obligatorio.',
            'file_code.unique' => 'Este código de expediente ya existe.',
            'file_title.required' => 'El título del expediente es obligatorio.',
            'application_type.required' => 'El tipo de solicitud es obligatorio.',
            'job_title.required' => 'El puesto de trabajo es obligatorio.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a hoy.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'salary.numeric' => 'El salario debe ser un número válido.',
            'salary.min' => 'El salario no puede ser negativo.',
            'working_hours.max' => 'Las horas semanales no pueden superar 60.',
            'employer_id.required' => 'El empleador es obligatorio.',
            'employer_id.exists' => 'El empleador seleccionado no existe.',
            'foreigner_id.required' => 'El trabajador extranjero es obligatorio.',
            'foreigner_id.exists' => 'El trabajador seleccionado no existe.',
        ];
    }
}
