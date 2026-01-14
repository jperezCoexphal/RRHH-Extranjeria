<?php

namespace App\Http\Requests\InmigrationFile;

use App\Enums\ApplicationType;
use App\Enums\WorkingDayType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInmigrationFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fileId = $this->route('id');

        return [
            'campaign' => ['sometimes', 'string', 'size:9'],
            'file_code' => [
                'sometimes',
                'string',
                'max:12',
                Rule::unique('inmigration_files', 'file_code')->ignore($fileId),
            ],
            'file_title' => ['sometimes', 'string', 'max:165'],
            'application_type' => ['sometimes', Rule::enum(ApplicationType::class)],
            'job_title' => ['sometimes', 'string', 'max:50'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'working_day_type' => ['nullable', Rule::enum(WorkingDayType::class)],
            'working_hours' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'probation_period' => ['nullable', 'integer', 'min:0', 'max:365'],
            'employer_id' => ['sometimes', 'integer', 'exists:employers,id'],
            'foreigner_id' => ['sometimes', 'integer', 'exists:foreigners,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign.size' => 'La campaña debe tener exactamente 9 caracteres.',
            'file_code.unique' => 'Este código de expediente ya existe.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'salary.numeric' => 'El salario debe ser un número válido.',
            'working_hours.max' => 'Las horas semanales no pueden superar 60.',
            'employer_id.exists' => 'El empleador seleccionado no existe.',
            'foreigner_id.exists' => 'El trabajador seleccionado no existe.',
        ];
    }
}
