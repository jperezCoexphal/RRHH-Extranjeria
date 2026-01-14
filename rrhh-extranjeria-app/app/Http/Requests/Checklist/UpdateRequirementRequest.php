<?php

namespace App\Http\Requests\Checklist;

use App\Enums\TargetEntity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_entity' => ['nullable', Rule::enum(TargetEntity::class)],
            'observation' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede superar 120 caracteres.',
            'description.max' => 'La descripción no puede superar 1000 caracteres.',
            'observation.max' => 'La observación no puede superar 1000 caracteres.',
        ];
    }
}
