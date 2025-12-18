<?php

namespace App\Http\Requests\Foreigner;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreForeignerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Foreigner main data
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:100'],
            'passport' => ['required', 'string', 'max:44', 'unique:foreigners,passport'],
            'nie' => ['required', 'string', 'size:9', 'unique:foreigners,nie'],
            'niss' => ['nullable', 'string', 'size:12', 'unique:foreigners,niss'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birthdate' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:50'],
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)],

            // Foreigner extra data
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'legal_guardian_name' => ['nullable', 'string', 'max:150'],
            'legal_guardian_identity_number' => ['nullable', 'string', 'max:44'],
            'guardianship_title' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'passport.required' => 'El pasaporte es obligatorio.',
            'passport.unique' => 'Este pasaporte ya está registrado.',
            'nie.required' => 'El NIE es obligatorio.',
            'nie.size' => 'El NIE debe tener exactamente 9 caracteres.',
            'nie.unique' => 'Este NIE ya está registrado.',
            'niss.size' => 'El NISS debe tener exactamente 12 caracteres.',
            'niss.unique' => 'Este NISS ya está registrado.',
            'gender.required' => 'El género es obligatorio.',
            'birthdate.required' => 'La fecha de nacimiento es obligatoria.',
            'birthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'nationality.required' => 'La nacionalidad es obligatoria.',
            'marital_status.required' => 'El estado civil es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
        ];
    }
}
