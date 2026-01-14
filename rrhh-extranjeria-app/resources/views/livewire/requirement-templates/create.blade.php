<?php

use App\Models\RequirementTemplate;
use App\Enums\TargetEntity;
use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Nueva Plantilla de Requisito - RRHH Extranjeria')]
class extends Component {
    public string $name = '';
    public string $description = '';
    public string $target_entity = '';
    public string $application_type = '';
    public string $trigger_status = '';
    public ?int $days_to_expire = null;
    public bool $is_mandatory = false;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'target_entity' => 'nullable|string',
            'application_type' => 'nullable|string',
            'trigger_status' => 'nullable|string',
            'days_to_expire' => 'nullable|integer|min:1|max:365',
            'is_mandatory' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        RequirementTemplate::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'target_entity' => $this->target_entity ?: null,
            'application_type' => $this->application_type ?: null,
            'trigger_status' => $this->trigger_status ?: null,
            'days_to_expire' => $this->days_to_expire,
            'is_mandatory' => $this->is_mandatory,
        ]);

        session()->flash('success', 'Plantilla creada correctamente.');
        $this->redirect(route('requirement-templates.index'), navigate: true);
    }

    public function saveAndNew(): void
    {
        $this->validate();

        RequirementTemplate::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'target_entity' => $this->target_entity ?: null,
            'application_type' => $this->application_type ?: null,
            'trigger_status' => $this->trigger_status ?: null,
            'days_to_expire' => $this->days_to_expire,
            'is_mandatory' => $this->is_mandatory,
        ]);

        session()->flash('success', 'Plantilla creada correctamente. Puedes crear otra.');

        // Reset form but keep some values for convenience
        $keepEntity = $this->target_entity;
        $keepType = $this->application_type;
        $keepStatus = $this->trigger_status;

        $this->reset(['name', 'description', 'days_to_expire', 'is_mandatory']);

        $this->target_entity = $keepEntity;
        $this->application_type = $keepType;
        $this->trigger_status = $keepStatus;
    }

    public function with(): array
    {
        return [
            'targetEntities' => TargetEntity::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'statuses' => ImmigrationFileStatus::cases(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Nueva Plantilla')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('requirement-templates.index') }}">Plantillas</a></li>
                    <li class="breadcrumb-item active">Nueva</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Nueva Plantilla de Requisito</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-clipboard-plus me-2"></i>
                        Datos de la Plantilla
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        {{-- Nombre y Descripcion --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">Nombre del Requisito <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Ej: Contrato de trabajo firmado">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripcion</label>
                                <textarea wire:model="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="2"
                                          placeholder="Descripcion opcional del requisito..."></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Configuracion --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Entidad Responsable</label>
                                <select wire:model="target_entity" class="form-select @error('target_entity') is-invalid @enderror">
                                    <option value="">Sin especificar</option>
                                    @foreach($targetEntities as $entity)
                                        <option value="{{ $entity->value }}">{{ $entity->label() }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Quien debe cumplir este requisito</small>
                                @error('target_entity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Solicitud</label>
                                <select wire:model="application_type" class="form-select @error('application_type') is-invalid @enderror">
                                    <option value="">Todos los tipos</option>
                                    @foreach($applicationTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Solo aplica a este tipo de expediente</small>
                                @error('application_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Estado Activador</label>
                                <select wire:model="trigger_status" class="form-select @error('trigger_status') is-invalid @enderror">
                                    <option value="">Manual (no automatico)</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Se genera al cambiar a este estado</small>
                                @error('trigger_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dias para Vencimiento</label>
                                <input type="number"
                                       wire:model="days_to_expire"
                                       class="form-control @error('days_to_expire') is-invalid @enderror"
                                       min="1"
                                       max="365"
                                       placeholder="Sin limite">
                                <small class="text-muted">Dias desde la creacion hasta vencimiento</small>
                                @error('days_to_expire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Obligatorio --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                       wire:model="is_mandatory"
                                       class="form-check-input"
                                       id="is_mandatory"
                                       role="switch">
                                <label class="form-check-label" for="is_mandatory">
                                    <strong>Requisito Obligatorio</strong>
                                    <br><small class="text-muted">Los requisitos obligatorios deben completarse para avanzar</small>
                                </label>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar
                            </button>
                            <button type="button" wire:click="saveAndNew" class="btn btn-outline-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                Guardar y Crear Otra
                            </button>
                            <a href="{{ route('requirement-templates.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar de Ayuda --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Ayuda
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold">Entidad Responsable</h6>
                    <ul class="small text-muted mb-3">
                        <li><strong>General:</strong> Requisitos del expediente</li>
                        <li><strong>Empleador:</strong> Documentos de la empresa</li>
                        <li><strong>Trabajador:</strong> Documentos del extranjero</li>
                        <li><strong>Representante:</strong> Documentos del gestor</li>
                    </ul>

                    <h6 class="fw-semibold">Estado Activador</h6>
                    <p class="small text-muted mb-3">
                        Si seleccionas un estado, el requisito se creara automaticamente cuando el expediente cambie a ese estado.
                        Si lo dejas vacio, solo se podra agregar manualmente.
                    </p>

                    <h6 class="fw-semibold">Dias para Vencimiento</h6>
                    <p class="small text-muted mb-0">
                        Establece una fecha limite automatica. El sistema alertara cuando el requisito este proximo a vencer.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Ejemplos
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="mb-2 pb-2 border-bottom">
                        <strong>Contrato firmado</strong><br>
                        <span class="text-muted">Empleador | Listo | 30 dias | Obligatorio</span>
                    </div>
                    <div class="mb-2 pb-2 border-bottom">
                        <strong>Pasaporte vigente</strong><br>
                        <span class="text-muted">Trabajador | Borrador | Sin limite | Obligatorio</span>
                    </div>
                    <div>
                        <strong>Certificado antecedentes</strong><br>
                        <span class="text-muted">Trabajador | Presentado | 90 dias | Opcional</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
