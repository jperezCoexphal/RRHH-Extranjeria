<?php

namespace App\Http\Requests\Employer;

use App\Enums\LegalForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employerId = $this->route('employer');
        $isFreelancer = $this->input('legal_form') === LegalForm::EI->value ||
                        $this->input('legal_form') === LegalForm::ERL->value;

        $baseRules = [
            'legal_form' => ['required', Rule::enum(LegalForm::class)],
            'fiscal_name' => ['required', 'string', 'max:100', Rule::unique('employers')->ignore($employerId)],
            'nif' => ['required', 'string', 'size:9', Rule::unique('employers')->ignore($employerId)],
            'ccc' => ['required', 'string', 'size:11'],
            'cnae' => ['required', 'string', 'size:5'],
            'is_associated' => ['required', 'boolean'],
            'comercial_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employers')->ignore($employerId)],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($isFreelancer) {
            $baseRules['first_name'] = ['required', 'string', 'max:50'];
            $baseRules['last_name'] = ['required', 'string', 'max:100'];
            $baseRules['niss'] = ['required', 'string', 'size:12', Rule::unique('freelancers', 'niss')->ignore($employerId, 'employer_id')];
            $baseRules['birthdate'] = ['required', 'date', 'before:today'];
        } else {
            $baseRules['representative_name'] = ['required', 'string', 'max:150'];
            $baseRules['representative_title'] = ['required', 'string', 'max:100'];
            $baseRules['representantive_identity_number'] = ['required', 'string', 'size:9', Rule::unique('companies', 'representantive_identity_number')->ignore($employerId, 'employer_id')];
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
