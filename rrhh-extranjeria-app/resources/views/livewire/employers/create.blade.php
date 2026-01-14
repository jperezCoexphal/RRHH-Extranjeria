<?php

use App\Models\Employer;
use App\Models\Country;
use App\Models\Province;
use App\Models\Municipality;
use App\Enums\LegalForm;
use App\Services\EmployerService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

new
#[Layout('layouts.app')]
#[Title('Nuevo Empleador - RRHH Extranjeria')]
class extends Component {
    // Datos empleador
    public string $legal_form = '';
    public string $comercial_name = '';
    public string $fiscal_name = '';
    public string $nif = '';
    public string $ccc = '';
    public string $cnae = '';
    public string $email = '';
    public string $phone = '';
    public bool $is_associated = false;

    // Datos persona fisica (freelancer)
    public string $first_name = '';
    public string $last_name = '';
    public string $niss = '';
    public string $birthdate = '';

    // Datos empresa (company)
    public string $representative_name = '';
    public string $representative_title = '';
    public string $representative_identity_number = '';

    // Direccion
    public string $street_name = '';
    public string $number = '';
    public string $floor_door = '';
    public string $postal_code = '';
    public string $country_id = '';
    public string $province_id = '';
    public string $municipality_id = '';

    public array $provinces = [];
    public array $municipalities = [];

    public function rules(): array
    {
        $rules = [
            'legal_form' => 'required|string',
            'comercial_name' => 'required|string|max:255',
            'fiscal_name' => 'nullable|string|max:255',
            'nif' => 'required|string|max:20|unique:employers,nif',
            'ccc' => 'nullable|string|max:20',
            'cnae' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'is_associated' => 'boolean',
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

        if ($this->isFreelancer()) {
            $rules['first_name'] = 'required|string|max:50';
            $rules['last_name'] = 'required|string|max:100';
            $rules['niss'] = 'nullable|string|max:12';
            $rules['birthdate'] = 'nullable|date';
        } else if ($this->legal_form !== '') {
            $rules['representative_name'] = 'required|string|max:150';
            $rules['representative_title'] = 'nullable|string|max:50';
            $rules['representative_identity_number'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    public function isFreelancer(): bool
    {
        return in_array($this->legal_form, [LegalForm::EI->value, LegalForm::ERL->value]);
    }

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

    public function save(): void
    {
        $validated = $this->validate();

        // Agregar campos de direccion (convertir strings vacios a null)
        $validated['country_id'] = $this->country_id !== '' ? $this->country_id : null;
        $validated['province_id'] = $this->province_id !== '' ? $this->province_id : null;
        $validated['municipality_id'] = $this->municipality_id !== '' ? $this->municipality_id : null;

        // Convertir strings vacios a null para campos opcionales
        foreach (['fiscal_name', 'ccc', 'cnae', 'email', 'phone', 'street_name', 'number', 'floor_door', 'postal_code', 'niss', 'birthdate', 'representative_title', 'representative_identity_number'] as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $service = app(EmployerService::class);

        try {
            $employer = $service->create($validated);
            session()->flash('success', 'Empleador creado correctamente.');
            $this->redirect(route('employers.show', $employer->id), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el empleador: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'legalForms' => LegalForm::cases(),
            'countries' => Country::orderBy('country_name')->get(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Nuevo Empleador')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('employers.index') }}">Empleadores</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Crear Empleador</h4>
        </div>
    </div>

    <form wire:submit="save">
        <div class="row">
            <div class="col-lg-8">
                {{-- Datos Principales --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-building me-2"></i>
                            Informacion General
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Forma Juridica <span class="text-danger">*</span></label>
                                <select wire:model.live="legal_form" class="form-select @error('legal_form') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($legalForms as $form)
                                        <option value="{{ $form->value }}">{{ $form->name }} - {{ $form->value }}</option>
                                    @endforeach
                                </select>
                                @error('legal_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIF <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nif" class="form-control @error('nif') is-invalid @enderror" placeholder="B12345678">
                                @error('nif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                                <input type="text" wire:model="comercial_name" class="form-control @error('comercial_name') is-invalid @enderror">
                                @error('comercial_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre Fiscal</label>
                                <input type="text" wire:model="fiscal_name" class="form-control @error('fiscal_name') is-invalid @enderror">
                                @error('fiscal_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CCC</label>
                                <input type="text" wire:model="ccc" class="form-control @error('ccc') is-invalid @enderror" placeholder="Codigo Cuenta Cotizacion">
                                @error('ccc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNAE</label>
                                <input type="text" wire:model="cnae" class="form-control @error('cnae') is-invalid @enderror">
                                @error('cnae') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4 pt-2">
                                    <input type="checkbox" wire:model="is_associated" class="form-check-input" id="is_associated">
                                    <label class="form-check-label" for="is_associated">Asociado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Datos Persona Fisica --}}
                @if($this->isFreelancer())
                    <div class="card mb-4">
                        <div class="card-header bg-info bg-opacity-10">
                            <h6 class="m-0 text-info">
                                <i class="bi bi-person me-2"></i>
                                Datos Persona Fisica
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror">
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="last_name" class="form-control @error('last_name') is-invalid @enderror">
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NISS</label>
                                    <input type="text" wire:model="niss" class="form-control @error('niss') is-invalid @enderror" placeholder="Numero Seguridad Social">
                                    @error('niss') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Nacimiento</label>
                                    <input type="date" wire:model="birthdate" class="form-control @error('birthdate') is-invalid @enderror">
                                    @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($legal_form)
                    {{-- Datos Empresa --}}
                    <div class="card mb-4">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h6 class="m-0 text-primary">
                                <i class="bi bi-briefcase me-2"></i>
                                Datos Representante Legal
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Representante <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="representative_name" class="form-control @error('representative_name') is-invalid @enderror">
                                    @error('representative_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIF/NIE Representante</label>
                                    <input type="text" wire:model="representative_identity_number" class="form-control @error('representative_identity_number') is-invalid @enderror">
                                    @error('representative_identity_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cargo</label>
                                    <input type="text" wire:model="representative_title" class="form-control @error('representative_title') is-invalid @enderror" placeholder="Ej: Administrador">
                                    @error('representative_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Direccion --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Direccion
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
                {{-- Contacto --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-telephone me-2"></i>
                            Contacto
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="empresa@ejemplo.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Telefono</label>
                            <input type="tel" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+34 600 000 000">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar Empleador
                            </button>
                            <a href="{{ route('employers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
