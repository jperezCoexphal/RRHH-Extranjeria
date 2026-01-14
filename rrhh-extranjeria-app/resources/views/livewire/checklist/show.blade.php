<?php

use App\Enums\TargetEntity;
use App\Models\FileRequirement;
use App\Models\InmigrationFile;
use App\Services\ChecklistService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Checklist del Expediente - RRHH Extranjeria')]
class extends Component {
    public InmigrationFile $file;
    public array $summary = [];
    public string $filterEntity = '';
    public string $filterStatus = '';

    // Form para nuevo requisito
    public string $newName = '';
    public string $newDescription = '';
    public string $newTargetEntity = '';
    public ?string $newDueDate = null;
    public bool $newIsMandatory = false;
    public bool $showAddForm = false;

    public function mount(int $inmigrationFileId): void
    {
        $this->file = InmigrationFile::with([
            'requirements',
            'employer',
            'foreigner'
        ])->findOrFail($inmigrationFileId);

        $this->loadSummary();
    }

    public function loadSummary(): void
    {
        $service = app(ChecklistService::class);
        $this->summary = $service->getChecklistSummary($this->file->id);
    }

    public function getRequirementsProperty()
    {
        $query = $this->file->requirements();

        if ($this->filterEntity) {
            $query->where('target_entity', $this->filterEntity);
        }

        if ($this->filterStatus === 'completed') {
            $query->where('is_completed', true);
        } elseif ($this->filterStatus === 'pending') {
            $query->where('is_completed', false);
        } elseif ($this->filterStatus === 'overdue') {
            $query->where('is_completed', false)
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now());
        }

        return $query->orderBy('is_completed')
                     ->orderBy('is_mandatory', 'desc')
                     ->orderBy('due_date')
                     ->orderBy('target_entity')
                     ->get();
    }

    public function completeRequirement(int $requirementId): void
    {
        $service = app(ChecklistService::class);
        $result = $service->completeRequirement($requirementId);

        if ($result['success']) {
            $this->file->refresh();
            $this->loadSummary();
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function uncompleteRequirement(int $requirementId): void
    {
        $requirement = FileRequirement::find($requirementId);

        if ($requirement && $requirement->is_completed) {
            $requirement->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);
            $this->file->refresh();
            $this->loadSummary();
            session()->flash('success', 'Requisito marcado como pendiente.');
        }
    }

    public function deleteRequirement(int $requirementId): void
    {
        $service = app(ChecklistService::class);
        $result = $service->deleteRequirement($requirementId);

        if ($result['success']) {
            $this->file->refresh();
            $this->loadSummary();
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function addRequirement(): void
    {
        $this->validate([
            'newName' => 'required|string|max:120',
            'newDescription' => 'nullable|string',
            'newTargetEntity' => 'nullable|string',
            'newDueDate' => 'nullable|date',
        ]);

        $service = app(ChecklistService::class);

        $targetEntity = $this->newTargetEntity
            ? TargetEntity::from($this->newTargetEntity)
            : null;

        $dueDate = $this->newDueDate
            ? \Carbon\Carbon::parse($this->newDueDate)
            : null;

        $result = $service->addManualRequirement(
            $this->file->id,
            $this->newName,
            $this->newDescription ?: null,
            $targetEntity,
            $dueDate,
            $this->newIsMandatory
        );

        if ($result['success']) {
            $this->resetForm();
            $this->file->refresh();
            $this->loadSummary();
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function regenerateRequirements(): void
    {
        $service = app(ChecklistService::class);
        $result = $service->regenerateRequirements($this->file->id);

        if ($result['success']) {
            $this->file->refresh();
            $this->loadSummary();
            session()->flash('success', "Requisitos regenerados. Creados: {$result['created_count']}, Actualizados: {$result['updated_count']}.");
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function resetForm(): void
    {
        $this->newName = '';
        $this->newDescription = '';
        $this->newTargetEntity = '';
        $this->newDueDate = null;
        $this->newIsMandatory = false;
        $this->showAddForm = false;
    }

    public function toggleAddForm(): void
    {
        $this->showAddForm = !$this->showAddForm;
        if (!$this->showAddForm) {
            $this->resetForm();
        }
    }
}; ?>

<div>
    @section('page-title', 'Checklist - ' . $file->file_code)

    {{-- Alertas Flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.index') }}" wire:navigate>Expedientes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.show', $file->id) }}" wire:navigate>{{ $file->file_code }}</a></li>
                    <li class="breadcrumb-item active">Checklist</li>
                </ol>
            </nav>
            <h4 class="mb-0">Checklist del Expediente</h4>
            <small class="text-muted">{{ $file->file_title }}</small>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="regenerateRequirements"
                    wire:confirm="Esto regenerara los requisitos desde las plantillas. ¿Continuar?"
                    class="btn btn-outline-secondary">
                <i class="bi bi-arrow-repeat me-1"></i>
                Regenerar
            </button>
            <a href="{{ route('inmigration-files.show', $file->id) }}" wire:navigate class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Columna Principal --}}
        <div class="col-lg-8">
            {{-- Resumen con Progreso --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Progreso General
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Completados</span>
                                    <span class="fw-bold">{{ $summary['completed'] ?? 0 }} / {{ $summary['total'] ?? 0 }}</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success"
                                         role="progressbar"
                                         style="width: {{ $summary['completion_percentage'] ?? 0 }}%"
                                         aria-valuenow="{{ $summary['completion_percentage'] ?? 0 }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        {{ $summary['completion_percentage'] ?? 0 }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="fs-4 fw-bold text-warning">{{ $summary['pending'] ?? 0 }}</div>
                                        <small class="text-muted">Pendientes</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="fs-4 fw-bold text-danger">{{ $summary['mandatory_pending'] ?? 0 }}</div>
                                        <small class="text-muted">Obligatorios</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="fs-4 fw-bold text-danger">{{ $summary['overdue'] ?? 0 }}</div>
                                        <small class="text-muted">Vencidos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(($summary['ready_for_documents'] ?? false))
                        <div class="alert alert-success mb-0 mt-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Listo!</strong> Todos los requisitos obligatorios estan completados.
                        </div>
                    @elseif(($summary['mandatory_pending'] ?? 0) > 0)
                        <div class="alert alert-warning mb-0 mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Faltan <strong>{{ $summary['mandatory_pending'] }}</strong> requisitos obligatorios por completar.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Progreso por Entidad --}}
            @if(!empty($summary['by_entity']))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Progreso por Seccion
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($summary['by_entity'] as $entityKey => $entityData)
                            @php
                                $entityPercentage = $entityData['total'] > 0
                                    ? round(($entityData['completed'] / $entityData['total']) * 100)
                                    : 0;
                                $progressClass = match(true) {
                                    $entityPercentage == 100 => 'bg-success',
                                    $entityPercentage >= 50 => 'bg-info',
                                    $entityPercentage > 0 => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">{{ $entityData['label'] }}</span>
                                    <span class="small fw-bold">{{ $entityData['completed'] }}/{{ $entityData['total'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $progressClass }}"
                                         style="width: {{ $entityPercentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Filtros --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-list-check me-2"></i>
                        Lista de Requisitos
                    </h6>
                    <button wire:click="toggleAddForm" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Nuevo Requisito
                    </button>
                </div>
                <div class="card-body border-bottom">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select wire:model.live="filterEntity" class="form-select form-select-sm">
                                <option value="">Todas las secciones</option>
                                @foreach(App\Enums\TargetEntity::cases() as $entity)
                                    <option value="{{ $entity->value }}">{{ $entity->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select wire:model.live="filterStatus" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pending">Pendientes</option>
                                <option value="completed">Completados</option>
                                <option value="overdue">Vencidos</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Formulario Nuevo Requisito --}}
                @if($showAddForm)
                <div class="card-body border-bottom bg-light">
                    <h6 class="mb-3">
                        <i class="bi bi-clipboard-plus me-2"></i>
                        Nuevo Requisito Manual
                    </h6>
                    <form wire:submit="addRequirement">
                        <div class="row g-3">
                            {{-- Nombre --}}
                            <div class="col-12">
                                <label class="form-label">Nombre del Requisito <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="newName"
                                       class="form-control @error('newName') is-invalid @enderror"
                                       placeholder="Ej: Contrato de trabajo firmado"
                                       required>
                                @error('newName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Descripcion --}}
                            <div class="col-12">
                                <label class="form-label">Descripcion</label>
                                <textarea wire:model="newDescription"
                                          class="form-control @error('newDescription') is-invalid @enderror"
                                          rows="2"
                                          placeholder="Descripcion opcional del requisito..."></textarea>
                                @error('newDescription')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Entidad y Fecha --}}
                            <div class="col-md-6">
                                <label class="form-label">Entidad Responsable</label>
                                <select wire:model="newTargetEntity" class="form-select @error('newTargetEntity') is-invalid @enderror">
                                    <option value="">Sin especificar</option>
                                    @foreach(App\Enums\TargetEntity::cases() as $entity)
                                        <option value="{{ $entity->value }}">{{ $entity->label() }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Quien debe cumplir este requisito</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Limite</label>
                                <input type="date"
                                       wire:model="newDueDate"
                                       class="form-control @error('newDueDate') is-invalid @enderror">
                                <small class="text-muted">Fecha maxima para completar</small>
                                @error('newDueDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Obligatorio --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox"
                                           wire:model="newIsMandatory"
                                           class="form-check-input"
                                           id="newMandatory"
                                           role="switch">
                                    <label class="form-check-label" for="newMandatory">
                                        <strong>Requisito Obligatorio</strong>
                                        <br><small class="text-muted">Los requisitos obligatorios deben completarse para generar documentos</small>
                                    </label>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Agregar Requisito
                                </button>
                                <button type="button" wire:click="toggleAddForm" class="btn btn-outline-secondary">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Lista de Requisitos --}}
                <div class="list-group list-group-flush">
                    @forelse($this->requirements as $requirement)
                        <div class="list-group-item {{ $requirement->is_completed ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-start">
                                    {{-- Checkbox --}}
                                    <div class="me-3">
                                        @if($requirement->is_completed)
                                            <button wire:click="uncompleteRequirement({{ $requirement->id }})"
                                                    class="btn btn-sm btn-success rounded-circle p-0"
                                                    style="width: 28px; height: 28px;"
                                                    title="Marcar como pendiente">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        @else
                                            <button wire:click="completeRequirement({{ $requirement->id }})"
                                                    class="btn btn-sm btn-outline-secondary rounded-circle p-0"
                                                    style="width: 28px; height: 28px;"
                                                    title="Marcar como completado">
                                                <i class="bi bi-circle"></i>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div>
                                        <div class="{{ $requirement->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                            <strong>{{ $requirement->name }}</strong>
                                            @if($requirement->is_mandatory)
                                                <span class="badge bg-danger ms-1">Obligatorio</span>
                                            @endif
                                            @if($requirement->requirement_template_id)
                                                <span class="badge bg-secondary ms-1" title="Generado desde plantilla">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </span>
                                            @endif
                                        </div>
                                        @if($requirement->description)
                                            <small class="text-muted d-block">{{ $requirement->description }}</small>
                                        @endif
                                        <div class="mt-1">
                                            @if($requirement->target_entity)
                                                <span class="badge bg-info me-1">{{ $requirement->target_entity->label() }}</span>
                                            @endif
                                            @if($requirement->due_date)
                                                @php
                                                    $isOverdue = !$requirement->is_completed && $requirement->due_date->isPast();
                                                    $dueSoon = !$requirement->is_completed && !$isOverdue && $requirement->due_date->diffInDays(now()) <= 3;
                                                @endphp
                                                <span class="badge {{ $isOverdue ? 'bg-danger' : ($dueSoon ? 'bg-warning' : 'bg-secondary') }}">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    {{ $requirement->due_date->format('d/m/Y') }}
                                                    @if($isOverdue) (Vencido) @endif
                                                </span>
                                            @endif
                                            @if($requirement->is_completed && $requirement->completed_at)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check me-1"></i>
                                                    {{ $requirement->completed_at->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div>
                                    @if(!$requirement->is_completed && !$requirement->requirement_template_id)
                                        <button wire:click="deleteRequirement({{ $requirement->id }})"
                                                wire:confirm="¿Eliminar este requisito?"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No hay requisitos que mostrar</p>
                            @if($filterEntity || $filterStatus)
                                <button wire:click="$set('filterEntity', ''); $set('filterStatus', '')"
                                        class="btn btn-sm btn-outline-primary mt-2">
                                    Limpiar filtros
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Info Expediente --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-folder2-open me-2"></i>
                        Expediente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Codigo</label>
                        <p class="mb-0 fw-bold">{{ $file->file_code }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Estado</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $file->status->value === 'favorable' ? 'success' : 'info' }}">
                                {{ $file->status->label() }}
                            </span>
                        </p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Tipo</label>
                        <p class="mb-0">{{ $file->application_type->value }}</p>
                    </div>
                </div>
            </div>

            {{-- Empleador --}}
            @if($file->employer)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-building me-2"></i>
                        Empleador
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-bold">{{ $file->employer->comercial_name }}</p>
                    <p class="mb-0 small text-muted">{{ $file->employer->nif }}</p>
                </div>
            </div>
            @endif

            {{-- Trabajador --}}
            @if($file->foreigner)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-person me-2"></i>
                        Trabajador
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-bold">{{ $file->foreigner->first_name }} {{ $file->foreigner->last_name }}</p>
                    <p class="mb-0 small text-muted">{{ $file->foreigner->nie }}</p>
                </div>
            </div>
            @endif

            {{-- Leyenda --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Leyenda
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="mb-2">
                        <span class="badge bg-danger">Obligatorio</span>
                        <span class="text-muted ms-2">Requisito obligatorio para generar documentos</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-secondary"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="text-muted ms-2">Generado desde plantilla</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger"><i class="bi bi-calendar"></i> Vencido</span>
                        <span class="text-muted ms-2">Fecha limite superada</span>
                    </div>
                    <div>
                        <span class="badge bg-warning"><i class="bi bi-calendar"></i></span>
                        <span class="text-muted ms-2">Proximo a vencer (3 dias)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
