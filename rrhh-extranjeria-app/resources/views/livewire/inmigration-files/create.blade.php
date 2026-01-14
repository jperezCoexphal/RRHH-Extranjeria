<?php

use App\Models\InmigrationFile;
use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\ForeignerExtraData;
use App\Models\Country;
use App\Models\Province;
use App\Models\Municipality;
use App\Enums\ApplicationType;
use App\Enums\WorkingDayType;
use App\Enums\LegalForm;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Services\EmployerService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new
#[Layout('layouts.app')]
#[Title('Nuevo Expediente - RRHH Extranjeria')]
class extends Component {
    // Datos expediente
    public string $campaign = '';
    public string $file_code = '';
    public string $file_title = '';
    public string $application_type = '';

    // Datos laborales
    public string $job_title = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $salary = '';
    public string $working_day_type = '';
    public string $working_hours = '';
    public string $probation_period = '';

    // Relaciones
    public string $employer_id = '';
    public string $foreigner_id = '';

    // Busqueda
    public string $employerSearch = '';
    public string $foreignerSearch = '';

    // Modo creacion inline
    public bool $showEmployerForm = false;
    public bool $showForeignerForm = false;

    // Datos nuevo empleador
    public string $emp_legal_form = '';
    public string $emp_comercial_name = '';
    public string $emp_fiscal_name = '';
    public string $emp_nif = '';
    public string $emp_ccc = '';
    public string $emp_cnae = '';
    public string $emp_email = '';
    public string $emp_phone = '';
    public bool $emp_is_associated = false;
    public string $emp_first_name = '';
    public string $emp_last_name = '';
    public string $emp_niss = '';
    public string $emp_birthdate = '';
    public string $emp_representative_name = '';
    public string $emp_representative_title = '';
    public string $emp_representative_identity_number = '';
    public string $emp_street_name = '';
    public string $emp_number = '';
    public string $emp_floor_door = '';
    public string $emp_postal_code = '';
    public string $emp_country_id = '';
    public string $emp_province_id = '';
    public string $emp_municipality_id = '';
    public array $emp_provinces = [];
    public array $emp_municipalities = [];

    // Datos nuevo extranjero
    public string $for_first_name = '';
    public string $for_last_name = '';
    public string $for_passport = '';
    public string $for_nie = '';
    public string $for_niss = '';
    public string $for_gender = '';
    public string $for_birthdate = '';
    public string $for_marital_status = '';
    public string $for_nationality_id = '';
    public string $for_birth_country_id = '';
    public string $for_birthplace_name = '';
    public string $for_father_name = '';
    public string $for_mother_name = '';
    public string $for_phone = '';
    public string $for_email = '';
    public string $for_street_name = '';
    public string $for_number = '';
    public string $for_floor_door = '';
    public string $for_postal_code = '';
    public string $for_country_id = '';
    public string $for_province_id = '';
    public string $for_municipality_id = '';
    public array $for_provinces = [];
    public array $for_municipalities = [];

    // Direccion trabajo
    public string $street_name = '';
    public string $number = '';
    public string $floor_door = '';
    public string $postal_code = '';
    public string $country_id = '';
    public string $province_id = '';
    public string $municipality_id = '';
    public array $provinces = [];
    public array $municipalities = [];

    public function mount(): void
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        if ($currentMonth >= 9) {
            $this->campaign = $currentYear . '-' . ($currentYear + 1);
        } else {
            $this->campaign = ($currentYear - 1) . '-' . $currentYear;
        }

        $lastFile = InmigrationFile::where('campaign', $this->campaign)
            ->orderBy('file_code', 'desc')
            ->first();

        if ($lastFile) {
            $lastNumber = (int) substr($lastFile->file_code, -4);
            $this->file_code = 'EXP-' . $this->campaign . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $this->file_code = 'EXP-' . $this->campaign . '-0001';
        }
    }

    public function rules(): array
    {
        $rules = [
            'campaign' => 'required|string|max:9',
            'file_code' => 'required|string|max:20|unique:inmigration_files,file_code',
            'file_title' => 'required|string|max:165',
            'application_type' => 'required|string',
            'job_title' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'nullable|numeric|min:0',
            'working_day_type' => 'nullable|string',
            'working_hours' => 'nullable|numeric|min:0|max:60',
            'probation_period' => 'nullable|integer|min:0',
            'employer_id' => 'required|exists:employers,id',
            'foreigner_id' => 'required|exists:foreigners,id',
            'street_name' => 'nullable|string|max:150',
            'number' => 'nullable|string|max:10',
            'floor_door' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:5',
        ];

        // Solo validar existencia si se proporciona un valor
        if ($this->country_id !== '') {
            $rules['country_id'] = 'exists:countries,id';
        }
        if ($this->province_id !== '') {
            $rules['province_id'] = 'exists:provinces,id';
        }
        if ($this->municipality_id !== '') {
            $rules['municipality_id'] = 'exists:municipalities,id';
        }

        return $rules;
    }

    // Cascading selects para direccion trabajo
    public function updatedCountryId(): void
    {
        $this->provinces = Province::where('country_id', $this->country_id)->get()->toArray();
        $this->province_id = '';
        $this->municipalities = [];
        $this->municipality_id = '';
    }

    public function updatedProvinceId(): void
    {
        $this->municipalities = Municipality::where('province_id', $this->province_id)->get()->toArray();
        $this->municipality_id = '';
    }

    // Cascading selects para empleador
    public function updatedEmpCountryId(): void
    {
        $this->emp_provinces = Province::where('country_id', $this->emp_country_id)->get()->toArray();
        $this->emp_province_id = '';
        $this->emp_municipalities = [];
        $this->emp_municipality_id = '';
    }

    public function updatedEmpProvinceId(): void
    {
        $this->emp_municipalities = Municipality::where('province_id', $this->emp_province_id)->get()->toArray();
        $this->emp_municipality_id = '';
    }

    // Cascading selects para extranjero
    public function updatedForCountryId(): void
    {
        $this->for_provinces = Province::where('country_id', $this->for_country_id)->get()->toArray();
        $this->for_province_id = '';
        $this->for_municipalities = [];
        $this->for_municipality_id = '';
    }

    public function updatedForProvinceId(): void
    {
        $this->for_municipalities = Municipality::where('province_id', $this->for_province_id)->get()->toArray();
        $this->for_municipality_id = '';
    }

    public function updatedEmployerId(): void
    {
        $this->generateTitle();
    }

    public function updatedForeignerId(): void
    {
        $this->generateTitle();
    }

    public function updatedApplicationType(): void
    {
        $this->generateTitle();
    }

    protected function generateTitle(): void
    {
        $parts = [];

        if ($this->application_type) {
            $type = ApplicationType::from($this->application_type);
            $parts[] = $type->name;
        }

        if ($this->employer_id) {
            $employer = Employer::find($this->employer_id);
            if ($employer) {
                $parts[] = $employer->comercial_name;
            }
        }

        if ($this->foreigner_id) {
            $foreigner = Foreigner::find($this->foreigner_id);
            if ($foreigner) {
                $parts[] = $foreigner->first_name . ' ' . $foreigner->last_name;
            }
        }

        $this->file_title = implode(' - ', $parts);
    }

    public function toggleEmployerForm(): void
    {
        $this->showEmployerForm = !$this->showEmployerForm;
        if ($this->showEmployerForm) {
            $this->employer_id = '';
            $this->employerSearch = '';
        }
    }

    public function toggleForeignerForm(): void
    {
        $this->showForeignerForm = !$this->showForeignerForm;
        if ($this->showForeignerForm) {
            $this->foreigner_id = '';
            $this->foreignerSearch = '';
        }
    }

    public function isFreelancer(): bool
    {
        return in_array($this->emp_legal_form, [LegalForm::EI->value, LegalForm::ERL->value]);
    }

    public function saveEmployer(): void
    {
        $rules = [
            'emp_legal_form' => 'required|string',
            'emp_fiscal_name' => 'required|string|max:255',
            'emp_comercial_name' => 'nullable|string|max:255',
            'emp_nif' => 'required|string|max:9|unique:employers,nif',
            'emp_ccc' => 'required|string|max:11',
            'emp_cnae' => 'required|string|max:4',
            'emp_email' => 'nullable|email|max:255',
            'emp_phone' => 'nullable|string|max:30',
        ];

        if ($this->isFreelancer()) {
            $rules['emp_first_name'] = 'required|string|max:50';
            $rules['emp_last_name'] = 'required|string|max:100';
            $rules['emp_niss'] = 'required|string|max:12';
            $rules['emp_birthdate'] = 'required|date';
        } else if ($this->emp_legal_form !== '') {
            $rules['emp_representative_name'] = 'required|string|max:150';
            $rules['emp_representative_title'] = 'required|string|max:100';
            $rules['emp_representative_identity_number'] = 'required|string|max:9';
        }

        $this->validate($rules);

        try {
            $data = [
                'legal_form' => $this->emp_legal_form,
                'comercial_name' => $this->emp_comercial_name ?: $this->emp_fiscal_name,
                'fiscal_name' => $this->emp_fiscal_name,
                'nif' => $this->emp_nif,
                'ccc' => $this->emp_ccc ?: null,
                'cnae' => $this->emp_cnae ?: null,
                'email' => $this->emp_email ?: null,
                'phone' => $this->emp_phone ?: null,
                'is_associated' => $this->emp_is_associated,
                'first_name' => $this->emp_first_name ?: null,
                'last_name' => $this->emp_last_name ?: null,
                'niss' => $this->emp_niss ?: null,
                'birthdate' => $this->emp_birthdate ?: null,
                'representative_name' => $this->emp_representative_name ?: null,
                'representative_title' => $this->emp_representative_title ?: null,
                'representative_identity_number' => $this->emp_representative_identity_number ?: null,
                'street_name' => $this->emp_street_name ?: null,
                'number' => $this->emp_number ?: null,
                'floor_door' => $this->emp_floor_door ?: null,
                'postal_code' => $this->emp_postal_code ?: null,
                'country_id' => $this->emp_country_id ?: null,
                'province_id' => $this->emp_province_id ?: null,
                'municipality_id' => $this->emp_municipality_id ?: null,
            ];

            $service = app(EmployerService::class);
            $employer = $service->create($data);

            $this->employer_id = (string) $employer->id;
            $this->showEmployerForm = false;
            $this->resetEmployerForm();
            $this->generateTitle();

            $this->dispatch('employer-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el empleador: ' . $e->getMessage());
        }
    }

    public function saveForeigner(): void
    {
        $this->validate([
            'for_first_name' => 'required|string|max:50',
            'for_last_name' => 'required|string|max:100',
            'for_passport' => 'required|string|max:44|unique:foreigners,passport',
            'for_nie' => 'required|string|max:9|unique:foreigners,nie',
            'for_gender' => 'required|string',
            'for_birthdate' => 'required|date',
            'for_marital_status' => 'required|string',
            'for_nationality_id' => 'required|exists:countries,id',
            'for_birth_country_id' => 'required|exists:countries,id',
            'for_birthplace_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $foreigner = Foreigner::create([
                'first_name' => $this->for_first_name,
                'last_name' => $this->for_last_name,
                'passport' => $this->for_passport,
                'nie' => $this->for_nie,
                'niss' => $this->for_niss ?: null,
                'gender' => $this->for_gender,
                'birthdate' => $this->for_birthdate,
                'marital_status' => $this->for_marital_status,
                'nationality_id' => $this->for_nationality_id,
                'birth_country_id' => $this->for_birth_country_id,
                'birthplace_name' => $this->for_birthplace_name,
            ]);

            ForeignerExtraData::create([
                'foreigner_id' => $foreigner->id,
                'father_name' => $this->for_father_name ?: null,
                'mother_name' => $this->for_mother_name ?: null,
                'phone' => $this->for_phone ?: null,
                'email' => $this->for_email ?: null,
            ]);

            if ($this->for_street_name && $this->for_country_id) {
                $foreigner->address()->create([
                    'street_name' => $this->for_street_name,
                    'number' => $this->for_number ?: null,
                    'floor_door' => $this->for_floor_door ?: null,
                    'postal_code' => $this->for_postal_code ?: '00000',
                    'country_id' => $this->for_country_id,
                    'province_id' => $this->for_province_id ?: null,
                    'municipality_id' => $this->for_municipality_id ?: null,
                ]);
            }

            DB::commit();

            $this->foreigner_id = (string) $foreigner->id;
            $this->showForeignerForm = false;
            $this->resetForeignerForm();
            $this->generateTitle();

            $this->dispatch('foreigner-created');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el extranjero: ' . $e->getMessage());
        }
    }

    protected function resetEmployerForm(): void
    {
        $this->emp_legal_form = '';
        $this->emp_comercial_name = '';
        $this->emp_fiscal_name = '';
        $this->emp_nif = '';
        $this->emp_ccc = '';
        $this->emp_cnae = '';
        $this->emp_email = '';
        $this->emp_phone = '';
        $this->emp_is_associated = false;
        $this->emp_first_name = '';
        $this->emp_last_name = '';
        $this->emp_niss = '';
        $this->emp_birthdate = '';
        $this->emp_representative_name = '';
        $this->emp_representative_title = '';
        $this->emp_representative_identity_number = '';
        $this->emp_street_name = '';
        $this->emp_number = '';
        $this->emp_floor_door = '';
        $this->emp_postal_code = '';
        $this->emp_country_id = '';
        $this->emp_province_id = '';
        $this->emp_municipality_id = '';
        $this->emp_provinces = [];
        $this->emp_municipalities = [];
    }

    protected function resetForeignerForm(): void
    {
        $this->for_first_name = '';
        $this->for_last_name = '';
        $this->for_passport = '';
        $this->for_nie = '';
        $this->for_niss = '';
        $this->for_gender = '';
        $this->for_birthdate = '';
        $this->for_marital_status = '';
        $this->for_nationality_id = '';
        $this->for_birth_country_id = '';
        $this->for_birthplace_name = '';
        $this->for_father_name = '';
        $this->for_mother_name = '';
        $this->for_phone = '';
        $this->for_email = '';
        $this->for_street_name = '';
        $this->for_number = '';
        $this->for_floor_door = '';
        $this->for_postal_code = '';
        $this->for_country_id = '';
        $this->for_province_id = '';
        $this->for_municipality_id = '';
        $this->for_provinces = [];
        $this->for_municipalities = [];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Agregar campos de direccion que pueden no estar en validated
        $countryId = $this->country_id !== '' ? $this->country_id : null;
        $provinceId = $this->province_id !== '' ? $this->province_id : null;
        $municipalityId = $this->municipality_id !== '' ? $this->municipality_id : null;

        DB::beginTransaction();
        try {
            $file = InmigrationFile::create([
                'campaign' => $validated['campaign'],
                'file_code' => $validated['file_code'],
                'file_title' => $validated['file_title'],
                'application_type' => $validated['application_type'],
                'status' => 'borrador',
                'job_title' => $validated['job_title'],
                'start_date' => $validated['start_date'],
                'end_date' => ($validated['end_date'] ?? '') ?: null,
                'salary' => ($validated['salary'] ?? '') ?: null,
                'working_day_type' => ($validated['working_day_type'] ?? '') ?: null,
                'working_hours' => ($validated['working_hours'] ?? '') ?: null,
                'probation_period' => ($validated['probation_period'] ?? '') ?: null,
                'employer_id' => $validated['employer_id'],
                'foreigner_id' => $validated['foreigner_id'],
                'editor_id' => Auth::id(),
            ]);

            $streetName = ($validated['street_name'] ?? '') ?: null;
            if ($streetName && $countryId) {
                $file->workAddress()->create([
                    'street_name' => $streetName,
                    'number' => ($validated['number'] ?? '') ?: null,
                    'floor_door' => ($validated['floor_door'] ?? '') ?: null,
                    'postal_code' => ($validated['postal_code'] ?? '') ?: '00000',
                    'country_id' => $countryId,
                    'province_id' => $provinceId,
                    'municipality_id' => $municipalityId,
                ]);
            }

            DB::commit();
            session()->flash('success', 'Expediente creado correctamente.');
            $this->redirect(route('inmigration-files.show', $file->id), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el expediente: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        $employersQuery = Employer::orderBy('comercial_name');
        if ($this->employerSearch) {
            $employersQuery->where(function ($q) {
                $q->where('comercial_name', 'like', "%{$this->employerSearch}%")
                  ->orWhere('nif', 'like', "%{$this->employerSearch}%");
            });
        }

        $foreignersQuery = Foreigner::orderBy('first_name');
        if ($this->foreignerSearch) {
            $foreignersQuery->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->foreignerSearch}%")
                  ->orWhere('last_name', 'like', "%{$this->foreignerSearch}%")
                  ->orWhere('nie', 'like', "%{$this->foreignerSearch}%");
            });
        }

        return [
            'employers' => $employersQuery->limit(50)->get(),
            'foreigners' => $foreignersQuery->limit(50)->get(),
            'applicationTypes' => ApplicationType::cases(),
            'workingDayTypes' => WorkingDayType::cases(),
            'countries' => Country::orderBy('country_name')->get(),
            'legalForms' => LegalForm::cases(),
            'genders' => Gender::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
            'selectedEmployer' => $this->employer_id ? Employer::find($this->employer_id) : null,
            'selectedForeigner' => $this->foreigner_id ? Foreigner::find($this->foreigner_id) : null,
        ];
    }
}; ?>

<div>
    @section('page-title', 'Nuevo Expediente')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.index') }}" wire:navigate>Expedientes</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Crear Expediente</h4>
        </div>
    </div>

    <form wire:submit="save">
        <div class="row">
            <div class="col-lg-8">
                {{-- Datos Expediente --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-folder2-open me-2"></i>
                            Informacion del Expediente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Campaña <span class="text-danger">*</span></label>
                                <input type="text" wire:model="campaign" class="form-control @error('campaign') is-invalid @enderror" readonly>
                                @error('campaign') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Codigo <span class="text-danger">*</span></label>
                                <input type="text" wire:model="file_code" class="form-control @error('file_code') is-invalid @enderror" readonly>
                                @error('file_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo Solicitud <span class="text-danger">*</span></label>
                                <select wire:model.live="application_type" class="form-select @error('application_type') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($applicationTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->name }} - {{ $type->description() }}</option>
                                    @endforeach
                                </select>
                                @error('application_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Titulo del Expediente <span class="text-danger">*</span></label>
                                <input type="text" wire:model="file_title" class="form-control @error('file_title') is-invalid @enderror">
                                @error('file_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Se genera automaticamente al seleccionar tipo, empleador y trabajador</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Partes Implicadas --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">
                            <i class="bi bi-people me-2"></i>
                            Partes Implicadas
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- EMPLEADOR --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Empleador <span class="text-danger">*</span></label>
                                <button type="button" wire:click="toggleEmployerForm" class="btn btn-sm {{ $showEmployerForm ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                    <i class="bi {{ $showEmployerForm ? 'bi-x-lg' : 'bi-plus-lg' }} me-1"></i>
                                    {{ $showEmployerForm ? 'Cancelar' : 'Nuevo Empleador' }}
                                </button>
                            </div>

                            @if(!$showEmployerForm)
                                {{-- Buscador y selector --}}
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="employerSearch" class="form-control" placeholder="Buscar por nombre o NIF...">
                                </div>
                                @if($selectedEmployer)
                                    <div class="alert alert-success py-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $selectedEmployer->comercial_name }}</strong>
                                                <small class="text-muted ms-2">{{ $selectedEmployer->nif }}</small>
                                            </div>
                                            <button type="button" wire:click="$set('employer_id', '')" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                <select wire:model.live="employer_id" class="form-select @error('employer_id') is-invalid @enderror" size="4">
                                    @forelse($employers as $employer)
                                        <option value="{{ $employer->id }}" @if($employer_id == $employer->id) selected @endif>
                                            {{ $employer->comercial_name }} ({{ $employer->nif }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No se encontraron empleadores</option>
                                    @endforelse
                                </select>
                                @error('employer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @else
                                {{-- Formulario inline empleador --}}
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-building me-2"></i>Nuevo Empleador
                                    </h6>
                                    <div class="row g-3">
                                        {{-- Datos Generales --}}
                                        <div class="col-md-6">
                                            <label class="form-label">Forma Juridica <span class="text-danger">*</span></label>
                                            <select wire:model.live="emp_legal_form" class="form-select form-select-sm @error('emp_legal_form') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach($legalForms as $form)
                                                    <option value="{{ $form->value }}">{{ $form->name }} - {{ $form->value }}</option>
                                                @endforeach
                                            </select>
                                            @error('emp_legal_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Asociado <span class="text-danger">*</span></label>
                                            <select wire:model="emp_is_associated" class="form-select form-select-sm @error('emp_is_associated') is-invalid @enderror">
                                                <option value="1">Si</option>
                                                <option value="0">No</option>
                                            </select>
                                            @error('emp_is_associated') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre Fiscal <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="emp_fiscal_name" class="form-control form-control-sm @error('emp_fiscal_name') is-invalid @enderror">
                                            @error('emp_fiscal_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre Comercial</label>
                                            <input type="text" wire:model="emp_comercial_name" class="form-control form-control-sm @error('emp_comercial_name') is-invalid @enderror">
                                            @error('emp_comercial_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">NIF <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="emp_nif" class="form-control form-control-sm @error('emp_nif') is-invalid @enderror" maxlength="9">
                                            @error('emp_nif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">CCC <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="emp_ccc" class="form-control form-control-sm @error('emp_ccc') is-invalid @enderror" maxlength="11">
                                            @error('emp_ccc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">CNAE <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="emp_cnae" class="form-control form-control-sm @error('emp_cnae') is-invalid @enderror" maxlength="4">
                                            @error('emp_cnae') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" wire:model="emp_email" class="form-control form-control-sm @error('emp_email') is-invalid @enderror">
                                            @error('emp_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Telefono</label>
                                            <input type="text" wire:model="emp_phone" class="form-control form-control-sm @error('emp_phone') is-invalid @enderror">
                                            @error('emp_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        {{-- Campos Autonomo --}}
                                        @if($this->isFreelancer())
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <h6 class="text-muted small mb-2">Datos del Autonomo</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_first_name" class="form-control form-control-sm @error('emp_first_name') is-invalid @enderror">
                                                @error('emp_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_last_name" class="form-control form-control-sm @error('emp_last_name') is-invalid @enderror">
                                                @error('emp_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NISS <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_niss" class="form-control form-control-sm @error('emp_niss') is-invalid @enderror" maxlength="12">
                                                @error('emp_niss') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                                <input type="date" wire:model="emp_birthdate" class="form-control form-control-sm @error('emp_birthdate') is-invalid @enderror">
                                                @error('emp_birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @elseif($emp_legal_form)
                                            {{-- Campos Empresa --}}
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <h6 class="text-muted small mb-2">Datos del Representante</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nombre del Representante <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_representative_name" class="form-control form-control-sm @error('emp_representative_name') is-invalid @enderror">
                                                @error('emp_representative_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cargo del Representante <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_representative_title" class="form-control form-control-sm @error('emp_representative_title') is-invalid @enderror">
                                                @error('emp_representative_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">DNI del Representante <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="emp_representative_identity_number" class="form-control form-control-sm @error('emp_representative_identity_number') is-invalid @enderror" maxlength="9">
                                                @error('emp_representative_identity_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endif

                                        <div class="col-12">
                                            <button type="button" wire:click="saveEmployer" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg me-1"></i>Guardar Empleador
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        {{-- EXTRANJERO --}}
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Trabajador Extranjero <span class="text-danger">*</span></label>
                                <button type="button" wire:click="toggleForeignerForm" class="btn btn-sm {{ $showForeignerForm ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                    <i class="bi {{ $showForeignerForm ? 'bi-x-lg' : 'bi-plus-lg' }} me-1"></i>
                                    {{ $showForeignerForm ? 'Cancelar' : 'Nuevo Trabajador' }}
                                </button>
                            </div>

                            @if(!$showForeignerForm)
                                {{-- Buscador y selector --}}
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="foreignerSearch" class="form-control" placeholder="Buscar por nombre o NIE...">
                                </div>
                                @if($selectedForeigner)
                                    <div class="alert alert-success py-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $selectedForeigner->first_name }} {{ $selectedForeigner->last_name }}</strong>
                                                <small class="text-muted ms-2">{{ $selectedForeigner->nie }}</small>
                                            </div>
                                            <button type="button" wire:click="$set('foreigner_id', '')" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                <select wire:model.live="foreigner_id" class="form-select @error('foreigner_id') is-invalid @enderror" size="4">
                                    @forelse($foreigners as $foreigner)
                                        <option value="{{ $foreigner->id }}" @if($foreigner_id == $foreigner->id) selected @endif>
                                            {{ $foreigner->first_name }} {{ $foreigner->last_name }} ({{ $foreigner->nie }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No se encontraron trabajadores</option>
                                    @endforelse
                                </select>
                                @error('foreigner_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @else
                                {{-- Formulario inline extranjero --}}
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-person me-2"></i>Nuevo Trabajador Extranjero
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="for_first_name" class="form-control form-control-sm @error('for_first_name') is-invalid @enderror">
                                            @error('for_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="for_last_name" class="form-control form-control-sm @error('for_last_name') is-invalid @enderror">
                                            @error('for_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">NIE <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="for_nie" class="form-control form-control-sm @error('for_nie') is-invalid @enderror" placeholder="Y1234567X">
                                            @error('for_nie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Pasaporte <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="for_passport" class="form-control form-control-sm @error('for_passport') is-invalid @enderror">
                                            @error('for_passport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">NISS</label>
                                            <input type="text" wire:model="for_niss" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Genero <span class="text-danger">*</span></label>
                                            <select wire:model="for_gender" class="form-select form-select-sm @error('for_gender') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach($genders as $g)
                                                    <option value="{{ $g->value }}">{{ $g->value }}</option>
                                                @endforeach
                                            </select>
                                            @error('for_gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="for_birthdate" class="form-control form-control-sm @error('for_birthdate') is-invalid @enderror">
                                            @error('for_birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Estado Civil <span class="text-danger">*</span></label>
                                            <select wire:model="for_marital_status" class="form-select form-select-sm @error('for_marital_status') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach($maritalStatuses as $status)
                                                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                                                @endforeach
                                            </select>
                                            @error('for_marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                                            <select wire:model="for_nationality_id" class="form-select form-select-sm @error('for_nationality_id') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('for_nationality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Pais Nacimiento <span class="text-danger">*</span></label>
                                            <select wire:model="for_birth_country_id" class="form-select form-select-sm @error('for_birth_country_id') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('for_birth_country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Lugar Nacimiento <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="for_birthplace_name" class="form-control form-control-sm @error('for_birthplace_name') is-invalid @enderror">
                                            @error('for_birthplace_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre del Padre</label>
                                            <input type="text" wire:model="for_father_name" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre de la Madre</label>
                                            <input type="text" wire:model="for_mother_name" class="form-control form-control-sm">
                                        </div>

                                        <div class="col-12">
                                            <button type="button" wire:click="saveForeigner" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg me-1"></i>Guardar Trabajador
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Datos Laborales --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-briefcase me-2"></i>
                            Datos Laborales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Puesto de Trabajo <span class="text-danger">*</span></label>
                                <input type="text" wire:model="job_title" class="form-control @error('job_title') is-invalid @enderror" placeholder="Ej: Peon Agricola">
                                @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salario Bruto Anual</label>
                                <div class="input-group">
                                    <input type="number" wire:model="salary" class="form-control @error('salary') is-invalid @enderror" step="0.01" min="0">
                                    <span class="input-group-text">EUR</span>
                                </div>
                                @error('salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Dejar vacio si es indefinido</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo Jornada</label>
                                <select wire:model="working_day_type" class="form-select @error('working_day_type') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($workingDayTypes as $type)
                                        <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                                    @endforeach
                                </select>
                                @error('working_day_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Horas Semanales</label>
                                <input type="number" wire:model="working_hours" class="form-control @error('working_hours') is-invalid @enderror" step="0.5" min="0" max="60">
                                @error('working_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lugar de Trabajo --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Lugar de Trabajo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Calle</label>
                                <input type="text" wire:model="street_name" class="form-control @error('street_name') is-invalid @enderror">
                                @error('street_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Numero</label>
                                <input type="text" wire:model="number" class="form-control @error('number') is-invalid @enderror">
                                @error('number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Piso/Pta</label>
                                <input type="text" wire:model="floor_door" class="form-control @error('floor_door') is-invalid @enderror">
                                @error('floor_door') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Codigo Postal</label>
                                <input type="text" wire:model="postal_code" class="form-control @error('postal_code') is-invalid @enderror" maxlength="5">
                                @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pais</label>
                                <select wire:model.live="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Provincia</label>
                                <select wire:model.live="province_id" class="form-select @error('province_id') is-invalid @enderror" @if(empty($provinces)) disabled @endif>
                                    <option value="">Seleccionar...</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['id'] }}">{{ $province['province_name'] }}</option>
                                    @endforeach
                                </select>
                                @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Municipio</label>
                                <select wire:model="municipality_id" class="form-select @error('municipality_id') is-invalid @enderror" @if(empty($municipalities)) disabled @endif>
                                    <option value="">Seleccionar...</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality['id'] }}">{{ $municipality['municipality_name'] }}</option>
                                    @endforeach
                                </select>
                                @error('municipality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Resumen --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Resumen
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-0">Estado Inicial</label>
                            <p class="mb-0"><span class="badge bg-secondary">Borrador</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-0">Gestor</label>
                            <p class="mb-0">{{ Auth::user()?->name ?? 'Sistema' }}</p>
                        </div>
                        @if($selectedEmployer)
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-0">Empleador</label>
                            <p class="mb-0">{{ $selectedEmployer->comercial_name }}</p>
                        </div>
                        @endif
                        @if($selectedForeigner)
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-0">Trabajador</label>
                            <p class="mb-0">{{ $selectedForeigner->first_name }} {{ $selectedForeigner->last_name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" @if($showEmployerForm || $showForeignerForm) disabled @endif>
                                <i class="bi bi-check-lg me-1"></i>
                                Crear Expediente
                            </button>
                            <a href="{{ route('inmigration-files.index') }}" wire:navigate class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancelar
                            </a>
                        </div>
                        @if($showEmployerForm || $showForeignerForm)
                            <small class="text-muted d-block mt-2 text-center">
                                Guarda o cancela el formulario abierto para continuar
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
