<?php

namespace App\Http\Requests\InmigrationFile;

use App\Enums\ImmigrationFileStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ImmigrationFileStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El nuevo estado es obligatorio.',
            'status.enum' => 'El estado seleccionado no es válido.',
        ];
    }
}
