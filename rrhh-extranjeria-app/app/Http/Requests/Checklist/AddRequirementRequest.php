<?php

namespace App\Http\Requests\Checklist;

use App\Enums\TargetEntity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_entity' => ['nullable', Rule::enum(TargetEntity::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'is_mandatory' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del requisito es obligatorio.',
            'name.max' => 'El nombre no puede superar 120 caracteres.',
            'description.max' => 'La descripción no puede superar 1000 caracteres.',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a hoy.',
        ];
    }
}
