<?php

namespace App\Http\Requests\Employer;

use App\Enums\LegalForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isFreelancer = $this->input('legal_form') === LegalForm::EI->value ||
                        $this->input('legal_form') === LegalForm::ERL->value;

        $baseRules = [
            'legal_form' => ['required', Rule::enum(LegalForm::class)],
            'fiscal_name' => ['required', 'string', 'max:100', 'unique:employers,fiscal_name'],
            'nif' => ['required', 'string', 'size:9', 'unique:employers,nif'],
            'ccc' => ['required', 'string', 'size:11'],
            'cnae' => ['required', 'string', 'size:4'],
            'is_associated' => ['required', 'boolean'],
            'comercial_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($isFreelancer) {
            $baseRules['first_name'] = ['required', 'string', 'max:50'];
            $baseRules['last_name'] = ['required', 'string', 'max:100'];
            $baseRules['niss'] = ['required', 'string', 'size:12', 'unique:freelancers,niss'];
            $baseRules['birthdate'] = ['required', 'date', 'before:today'];
        } else {
            $baseRules['representative_name'] = ['required', 'string', 'max:150'];
            $baseRules['representative_title'] = ['required', 'string', 'max:100'];
            $baseRules['representantive_identity_number'] = ['required', 'string', 'size:9', 'unique:companies,representantive_identity_number'];
        }

        return $baseRules;
    }

    public function messages(): array
    {
        return [
            'legal_form.required' => 'La forma legal es obligatoria.',
            'fiscal_name.required' => 'El nombre fiscal es obligatorio.',
            'fiscal_name.unique' => 'Este nombre fiscal ya está registrado.',
            'nif.required' => 'El NIF es obligatorio.',
            'nif.size' => 'El NIF debe tener exactamente 9 caracteres.',
            'nif.unique' => 'Este NIF ya está registrado.',
            'ccc.required' => 'El CCC es obligatorio.',
            'ccc.size' => 'El CCC debe tener exactamente 11 caracteres.',
            'cnae.required' => 'El CNAE es obligatorio.',
            'cnae.size' => 'El CNAE debe tener exactamente 5 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',
            'niss.size' => 'El NISS debe tener exactamente 12 caracteres.',
            'niss.unique' => 'Este NISS ya está registrado.',
            'birthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'representantive_identity_number.unique' => 'Este DNI del representante ya está registrado.',
        ];
    }
}
