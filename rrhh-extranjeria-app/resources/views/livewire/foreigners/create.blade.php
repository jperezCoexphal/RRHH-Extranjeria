<?php

use App\Models\Foreigner;
use App\Models\ForeignerExtraData;
use App\Models\Country;
use App\Models\Province;
use App\Models\Municipality;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

new
#[Layout('layouts.app')]
#[Title('Nuevo Extranjero - RRHH Extranjeria')]
class extends Component {
    // Datos personales
    public string $first_name = '';
    public string $last_name = '';
    public string $passport = '';
    public string $nie = '';
    public string $niss = '';
    public string $gender = '';
    public string $birthdate = '';
    public string $marital_status = '';
    public string $nationality_id = '';
    public string $birth_country_id = '';
    public string $birthplace_name = '';

    // Datos adicionales
    public string $father_name = '';
    public string $mother_name = '';
    public string $legal_guardian_name = '';
    public string $legal_guardian_identity_number = '';
    public string $guardianship_title = '';
    public string $phone = '';
    public string $email = '';

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
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:100',
            'passport' => 'required|string|max:44|unique:foreigners,passport',
            'nie' => 'required|string|max:9|unique:foreigners,nie',
            'niss' => 'nullable|string|max:12|unique:foreigners,niss',
            'gender' => 'required|string',
            'birthdate' => 'required|date',
            'marital_status' => 'required|string',
            'nationality_id' => 'required|exists:countries,id',
            'birth_country_id' => 'required|exists:countries,id',
            'birthplace_name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'legal_guardian_name' => 'nullable|string|max:150',
            'legal_guardian_identity_number' => 'nullable|string|max:44',
            'guardianship_title' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'street_name' => 'nullable|string|max:150',
            'number' => 'nullable|string|max:10',
            'floor_door' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:5',
            'country_id' => 'nullable|exists:countries,id',
            'province_id' => 'nullable|exists:provinces,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
        ];
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

        DB::beginTransaction();
        try {
            $foreigner = Foreigner::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'passport' => $validated['passport'],
                'nie' => $validated['nie'],
                'niss' => $validated['niss'] ?: null,
                'gender' => $validated['gender'],
                'birthdate' => $validated['birthdate'],
                'marital_status' => $validated['marital_status'],
                'nationality_id' => $validated['nationality_id'],
                'birth_country_id' => $validated['birth_country_id'],
                'birthplace_name' => $validated['birthplace_name'],
            ]);

            // Crear datos adicionales
            ForeignerExtraData::create([
                'foreigner_id' => $foreigner->id,
                'father_name' => $validated['father_name'] ?: null,
                'mother_name' => $validated['mother_name'] ?: null,
                'legal_guardian_name' => $validated['legal_guardian_name'] ?: null,
                'legal_guardian_identity_number' => $validated['legal_guardian_identity_number'] ?: null,
                'guardianship_title' => $validated['guardianship_title'] ?: null,
                'phone' => $validated['phone'] ?: null,
                'email' => $validated['email'] ?: null,
            ]);

            // Crear direccion si hay datos
            if ($validated['street_name'] && $validated['country_id']) {
                $foreigner->address()->create([
                    'street_name' => $validated['street_name'],
                    'number' => $validated['number'] ?: null,
                    'floor_door' => $validated['floor_door'] ?: null,
                    'postal_code' => $validated['postal_code'] ?? '00000',
                    'country_id' => $validated['country_id'],
                    'province_id' => $validated['province_id'] ?: null,
                    'municipality_id' => $validated['municipality_id'] ?: null,
                ]);
            }

            DB::commit();
            session()->flash('success', 'Extranjero creado correctamente.');
            $this->redirect(route('foreigners.show', $foreigner->id), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el extranjero: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'countries' => Country::orderBy('country_name')->get(),
            'genders' => Gender::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Nuevo Extranjero')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('foreigners.index') }}">Extranjeros</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Crear Extranjero</h4>
        </div>
    </div>

    <form wire:submit="save">
        <div class="row">
            <div class="col-lg-8">
                {{-- Datos Personales --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-person me-2"></i>
                            Datos Personales
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
                            <div class="col-md-4">
                                <label class="form-label">NIE <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nie" class="form-control @error('nie') is-invalid @enderror" placeholder="Y1234567X">
                                @error('nie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pasaporte <span class="text-danger">*</span></label>
                                <input type="text" wire:model="passport" class="form-control @error('passport') is-invalid @enderror">
                                @error('passport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NISS</label>
                                <input type="text" wire:model="niss" class="form-control @error('niss') is-invalid @enderror">
                                @error('niss') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Genero <span class="text-danger">*</span></label>
                                <select wire:model="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($genders as $g)
                                        <option value="{{ $g->value }}">{{ $g->value }}</option>
                                    @endforeach
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" wire:model="birthdate" class="form-control @error('birthdate') is-invalid @enderror">
                                @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado Civil <span class="text-danger">*</span></label>
                                <select wire:model="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($maritalStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->value }}</option>
                                    @endforeach
                                </select>
                                @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                                <select wire:model="nationality_id" class="form-select @error('nationality_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pais Nacimiento <span class="text-danger">*</span></label>
                                <select wire:model="birth_country_id" class="form-select @error('birth_country_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('birth_country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lugar Nacimiento <span class="text-danger">*</span></label>
                                <input type="text" wire:model="birthplace_name" class="form-control @error('birthplace_name') is-invalid @enderror">
                                @error('birthplace_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Datos Familiares --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-people me-2"></i>
                            Datos Familiares
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Padre</label>
                                <input type="text" wire:model="father_name" class="form-control @error('father_name') is-invalid @enderror">
                                @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre de la Madre</label>
                                <input type="text" wire:model="mother_name" class="form-control @error('mother_name') is-invalid @enderror">
                                @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12"><hr class="my-2"><small class="text-muted">Tutor Legal (si aplica)</small></div>
                            <div class="col-md-4">
                                <label class="form-label">Nombre Tutor</label>
                                <input type="text" wire:model="legal_guardian_name" class="form-control @error('legal_guardian_name') is-invalid @enderror">
                                @error('legal_guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Doc. Identidad Tutor</label>
                                <input type="text" wire:model="legal_guardian_identity_number" class="form-control @error('legal_guardian_identity_number') is-invalid @enderror">
                                @error('legal_guardian_identity_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Titulo Tutela</label>
                                <input type="text" wire:model="guardianship_title" class="form-control @error('guardianship_title') is-invalid @enderror">
                                @error('guardianship_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Direccion --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Direccion en España
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
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Telefono</label>
                            <input type="tel" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
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
                                Guardar Extranjero
                            </button>
                            <a href="{{ route('foreigners.index') }}" class="btn btn-outline-secondary">
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
