<?php

use App\Models\InmigrationFile;
use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\Country;
use App\Models\Province;
use App\Models\Municipality;
use App\Enums\ApplicationType;
use App\Enums\WorkingDayType;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

new
#[Layout('layouts.app')]
#[Title('Editar Expediente - RRHH Extranjeria')]
class extends Component {
    public InmigrationFile $file;

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

    public function mount(InmigrationFile $inmigrationFile): void
    {
        $this->file = $inmigrationFile->load('workAddress');

        // Cargar datos expediente
        $this->campaign = $this->file->campaign;
        $this->file_code = $this->file->file_code;
        $this->file_title = $this->file->file_title;
        $this->application_type = $this->file->application_type->value;

        // Cargar datos laborales
        $this->job_title = $this->file->job_title;
        $this->start_date = $this->file->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $this->file->end_date?->format('Y-m-d') ?? '';
        $this->salary = $this->file->salary ?? '';
        $this->working_day_type = $this->file->working_day_type?->value ?? '';
        $this->working_hours = $this->file->working_hours ?? '';
        $this->probation_period = $this->file->probation_period ?? '';

        // Cargar relaciones
        $this->employer_id = (string) $this->file->employer_id;
        $this->foreigner_id = (string) $this->file->foreigner_id;

        // Cargar direccion
        if ($this->file->workAddress) {
            $this->street_name = $this->file->workAddress->street_name ?? '';
            $this->number = $this->file->workAddress->number ?? '';
            $this->floor_door = $this->file->workAddress->floor_door ?? '';
            $this->postal_code = $this->file->workAddress->postal_code ?? '';
            $this->country_id = (string) ($this->file->workAddress->country_id ?? '');
            $this->province_id = (string) ($this->file->workAddress->province_id ?? '');
            $this->municipality_id = (string) ($this->file->workAddress->municipality_id ?? '');

            if ($this->country_id) {
                $this->provinces = Province::where('country_id', $this->country_id)->get()->toArray();
            }
            if ($this->province_id) {
                $this->municipalities = Municipality::where('province_id', $this->province_id)->get()->toArray();
            }
        }
    }

    public function rules(): array
    {
        return [
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
            $this->file->update([
                'file_title' => $validated['file_title'],
                'application_type' => $validated['application_type'],
                'job_title' => $validated['job_title'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?: null,
                'salary' => $validated['salary'] ?: null,
                'working_day_type' => $validated['working_day_type'] ?: null,
                'working_hours' => $validated['working_hours'] ?: null,
                'probation_period' => $validated['probation_period'] ?: null,
                'employer_id' => $validated['employer_id'],
                'foreigner_id' => $validated['foreigner_id'],
            ]);

            // Actualizar o crear direccion
            if ($validated['street_name'] && $validated['country_id']) {
                $this->file->workAddress()->updateOrCreate(
                    ['addressable_id' => $this->file->id, 'addressable_type' => InmigrationFile::class],
                    [
                        'street_name' => $validated['street_name'],
                        'number' => $validated['number'] ?: null,
                        'floor_door' => $validated['floor_door'] ?: null,
                        'postal_code' => $validated['postal_code'] ?? '00000',
                        'country_id' => $validated['country_id'],
                        'province_id' => $validated['province_id'] ?: null,
                        'municipality_id' => $validated['municipality_id'] ?: null,
                    ]
                );
            }

            DB::commit();
            session()->flash('success', 'Expediente actualizado correctamente.');
            $this->redirect(route('inmigration-files.show', $this->file->id), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar el expediente: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'employers' => Employer::orderBy('comercial_name')->get(),
            'foreigners' => Foreigner::orderBy('first_name')->get(),
            'applicationTypes' => ApplicationType::cases(),
            'workingDayTypes' => WorkingDayType::cases(),
            'countries' => Country::orderBy('country_name')->get(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Editar Expediente')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.index') }}">Expedientes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.show', $file->id) }}">{{ $file->file_code }}</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Editar Expediente {{ $file->file_code }}</h4>
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
                                <label class="form-label">Campaña</label>
                                <input type="text" value="{{ $campaign }}" class="form-control" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Codigo</label>
                                <input type="text" value="{{ $file_code }}" class="form-control" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo Solicitud <span class="text-danger">*</span></label>
                                <select wire:model="application_type" class="form-select @error('application_type') is-invalid @enderror">
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
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Partes Implicadas --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-people me-2"></i>
                            Partes Implicadas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Empleador <span class="text-danger">*</span></label>
                                <select wire:model="employer_id" class="form-select @error('employer_id') is-invalid @enderror">
                                    <option value="">Seleccionar empleador...</option>
                                    @foreach($employers as $employer)
                                        <option value="{{ $employer->id }}">{{ $employer->comercial_name }} ({{ $employer->nif }})</option>
                                    @endforeach
                                </select>
                                @error('employer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trabajador Extranjero <span class="text-danger">*</span></label>
                                <select wire:model="foreigner_id" class="form-select @error('foreigner_id') is-invalid @enderror">
                                    <option value="">Seleccionar trabajador...</option>
                                    @foreach($foreigners as $foreigner)
                                        <option value="{{ $foreigner->id }}">{{ $foreigner->first_name }} {{ $foreigner->last_name }} ({{ $foreigner->nie }})</option>
                                    @endforeach
                                </select>
                                @error('foreigner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
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
                                <input type="text" wire:model="job_title" class="form-control @error('job_title') is-invalid @enderror">
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
                            <div class="col-md-3">
                                <label class="form-label">Periodo Prueba (dias)</label>
                                <input type="number" wire:model="probation_period" class="form-control @error('probation_period') is-invalid @enderror" min="0">
                                @error('probation_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                {{-- Acciones --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar Cambios
                            </button>
                            <a href="{{ route('inmigration-files.show', $file->id) }}" class="btn btn-outline-secondary">
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
