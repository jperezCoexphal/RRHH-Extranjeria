<?php

use App\Models\InmigrationFile;
use App\Models\FileRequirement;
use App\Models\RequirementTemplate;
use App\Enums\ImmigrationFileStatus;
use App\Enums\TargetEntity;
use App\Services\ChecklistService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Detalle Expediente - RRHH Extranjeria')]
class extends Component {
    public InmigrationFile $file;

    // Desplegables abiertos por entidad
    public array $openSections = [];

    // Formulario nuevo requisito
    public string $addingToEntity = '';
    public string $req_name = '';
    public string $req_description = '';
    public string $req_due_date = '';
    public bool $req_is_mandatory = false;
    public bool $req_save_as_template = false;

    public function mount(InmigrationFile $inmigrationFile): void
    {
        $this->file = $inmigrationFile->load([
            'employer.company',
            'employer.freelancer',
            'foreigner',
            'editor',
            'requirements',
            'workAddress.country',
            'workAddress.province',
            'workAddress.municipality'
        ]);
    }

    public function toggleSection(string $entity): void
    {
        if (in_array($entity, $this->openSections)) {
            $this->openSections = array_filter($this->openSections, fn($e) => $e !== $entity);
        } else {
            $this->openSections[] = $entity;
        }
    }

    public function changeStatus(string $newStatus): void
    {
        $status = ImmigrationFileStatus::from($newStatus);

        if (!$this->file->status->canTransitionTo($status)) {
            session()->flash('error', 'No se puede cambiar a ese estado desde el estado actual.');
            return;
        }

        $checklistService = app(ChecklistService::class);
        $result = $checklistService->processStatusChange($this->file->id, $status);

        if ($result['success']) {
            $this->file->refresh();
            $this->file->load(['requirements']);
            $reqCount = count($result['requirements_created']);
            $message = 'Estado actualizado correctamente.';
            if ($reqCount > 0) {
                $message .= " Se generaron {$reqCount} requisitos.";
            }
            session()->flash('success', $message);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function toggleRequirement(int $requirementId): void
    {
        $requirement = FileRequirement::find($requirementId);

        if (!$requirement || $requirement->inmigration_file_id !== $this->file->id) {
            return;
        }

        if ($requirement->is_completed) {
            $requirement->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);
        } else {
            $requirement->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        $this->file->load(['requirements']);
    }

    public function startAddingRequirement(string $entity): void
    {
        $this->addingToEntity = $entity;
        $this->resetRequirementForm();
    }

    public function cancelAddingRequirement(): void
    {
        $this->addingToEntity = '';
        $this->resetRequirementForm();
    }

    public function saveRequirement(): void
    {
        $this->validate([
            'req_name' => 'required|string|max:255',
            'req_description' => 'nullable|string|max:500',
            'req_due_date' => 'nullable|date',
        ]);

        $checklistService = app(ChecklistService::class);
        $targetEntity = $this->addingToEntity ? TargetEntity::from($this->addingToEntity) : null;
        $dueDate = $this->req_due_date ? \Carbon\Carbon::parse($this->req_due_date) : null;

        $result = $checklistService->addManualRequirement(
            $this->file->id,
            $this->req_name,
            $this->req_description ?: null,
            $targetEntity,
            $dueDate,
            $this->req_is_mandatory
        );

        if ($result['success']) {
            // Si se marco guardar como plantilla, crear el template
            if ($this->req_save_as_template) {
                RequirementTemplate::create([
                    'name' => $this->req_name,
                    'description' => $this->req_description ?: null,
                    'target_entity' => $targetEntity,
                    'application_type' => $this->file->application_type,
                    'trigger_status' => $this->file->status,
                    'days_to_expire' => null,
                    'is_mandatory' => $this->req_is_mandatory,
                ]);
            }

            $this->file->load(['requirements']);
            $this->addingToEntity = '';
            $this->resetRequirementForm();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function deleteRequirement(int $requirementId): void
    {
        $checklistService = app(ChecklistService::class);
        $result = $checklistService->deleteRequirement($requirementId);

        if ($result['success']) {
            $this->file->load(['requirements']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    protected function resetRequirementForm(): void
    {
        $this->req_name = '';
        $this->req_description = '';
        $this->req_due_date = '';
        $this->req_is_mandatory = false;
        $this->req_save_as_template = false;
    }

    public function delete(): void
    {
        $this->file->delete();
        session()->flash('success', 'Expediente eliminado correctamente.');
        $this->redirect(route('inmigration-files.index'), navigate: true);
    }

    public function with(): array
    {
        $availableTransitions = [];
        foreach (ImmigrationFileStatus::cases() as $status) {
            if ($this->file->status->canTransitionTo($status)) {
                $availableTransitions[] = $status;
            }
        }

        // Agrupar requisitos por entidad
        $allReqs = $this->file->requirements;
        $reqsByEntity = [];
        foreach (TargetEntity::cases() as $entity) {
            $entityReqs = $allReqs->filter(fn($r) => $r->target_entity?->value === $entity->value);
            $reqsByEntity[$entity->value] = [
                'label' => $entity->label(),
                'items' => $entityReqs,
                'total' => $entityReqs->count(),
                'completed' => $entityReqs->where('is_completed', true)->count(),
                'pending' => $entityReqs->where('is_completed', false)->count(),
                'overdue' => $entityReqs->filter(fn($r) => !$r->is_completed && $r->due_date?->isPast())->count(),
            ];
        }

        // Resumen global
        $summary = [
            'total' => $allReqs->count(),
            'completed' => $allReqs->where('is_completed', true)->count(),
            'pending' => $allReqs->where('is_completed', false)->count(),
            'mandatory_pending' => $allReqs->where('is_completed', false)->where('is_mandatory', true)->count(),
            'overdue' => $allReqs->filter(fn($r) => !$r->is_completed && $r->due_date?->isPast())->count(),
        ];
        $summary['percentage'] = $summary['total'] > 0
            ? round(($summary['completed'] / $summary['total']) * 100)
            : 0;

        return [
            'availableTransitions' => $availableTransitions,
            'reqsByEntity' => $reqsByEntity,
            'summary' => $summary,
        ];
    }
}; ?>

<div>
    @section('page-title', 'Detalle Expediente')

    @php
        $statusColors = [
            'borrador' => 'secondary',
            'pendiente_revision' => 'warning',
            'listo' => 'info',
            'presentado' => 'primary',
            'requerido' => 'danger',
            'favorable' => 'success',
            'denegado' => 'danger',
            'archivado' => 'dark',
        ];

        // Helper para renderizar info del desplegable
        $getDropdownInfo = function($entityData) {
            $hasOverdue = $entityData['overdue'] > 0;
            $allCompleted = $entityData['total'] > 0 && $entityData['pending'] === 0;
            return [
                'badgeClass' => $hasOverdue ? 'bg-danger' : ($allCompleted ? 'bg-success' : ($entityData['total'] > 0 ? 'bg-warning' : 'bg-secondary')),
                'badgeText' => $entityData['completed'] . '/' . $entityData['total'],
            ];
        };
    @endphp

    {{-- Header --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('inmigration-files.index') }}">Expedientes</a></li>
                <li class="breadcrumb-item active">{{ $file->file_code }}</li>
            </ol>
        </nav>
        <h4 class="text-gray-800 mb-0">{{ $file->file_code }}</h4>
    </div>

    {{-- Stepper de Estados --}}
    @php
        $currentStatus = $file->status->value;
        $mainSteps = [
            ['key' => 'borrador', 'label' => 'Borrador'],
            ['key' => 'pendiente_revision', 'label' => 'Revision'],
            ['key' => 'listo', 'label' => 'Listo'],
            ['key' => 'presentado', 'label' => 'Presentado'],
            ['key' => 'resolucion', 'label' => 'Resolucion'],
        ];

        $currentIndex = match($currentStatus) {
            'borrador' => 0,
            'pendiente_revision' => 1,
            'listo' => 2,
            'presentado', 'requerido' => 3,
            'favorable', 'denegado' => 4,
            'archivado' => 5,
            default => 0,
        };

        $isRequerido = $currentStatus === 'requerido';
        $isFavorable = $currentStatus === 'favorable';
        $isDenegado = $currentStatus === 'denegado';
        $isArchivado = $currentStatus === 'archivado';
    @endphp

    <div class="d-flex align-items-center mb-4 py-2">
        @foreach($mainSteps as $index => $step)
            @php
                $isCompleted = $index < $currentIndex;
                $isCurrent = $index === $currentIndex;

                if ($step['key'] === 'resolucion' && $isCurrent) {
                    $dotColor = $isFavorable ? 'bg-success' : ($isDenegado ? 'bg-danger' : 'bg-primary');
                    $textClass = $isFavorable ? 'text-success' : ($isDenegado ? 'text-danger' : 'text-primary');
                    $label = $isFavorable ? 'Favorable' : ($isDenegado ? 'Denegado' : $step['label']);
                } else {
                    $dotColor = $isCompleted ? 'bg-success' : ($isCurrent ? 'bg-primary' : 'bg-secondary opacity-25');
                    $textClass = $isCompleted ? 'text-success' : ($isCurrent ? 'text-primary fw-semibold' : 'text-muted');
                    $label = $step['label'];
                }
            @endphp

            {{-- Step --}}
            <div class="d-flex align-items-center">
                <span class="rounded-circle {{ $dotColor }}" style="width: 10px; height: 10px;"></span>
                <span class="ms-2 small {{ $textClass }}">{{ $label }}</span>
            </div>

            {{-- Linea conectora --}}
            @if($index < count($mainSteps) - 1)
                <div class="flex-grow-1 mx-3" style="height: 2px; background: {{ $isCompleted ? '#198754' : '#dee2e6' }};"></div>
            @endif
        @endforeach

        {{-- Indicador Archivado --}}
        @if($isArchivado)
            <div class="flex-grow-1 mx-3" style="height: 2px; background: #198754;"></div>
            <div class="d-flex align-items-center">
                <span class="rounded-circle bg-dark" style="width: 10px; height: 10px;"></span>
                <span class="ms-2 small text-dark fw-semibold">Archivado</span>
            </div>
        @endif

        {{-- Badge Requerido --}}
        @if($isRequerido)
            <span class="badge bg-warning text-dark ms-3">
                <i class="bi bi-exclamation-triangle me-1"></i>Subsanacion
            </span>
        @endif
    </div>

    <div class="row">
        {{-- Datos Principales --}}
        <div class="col-lg-8">
            {{-- Info General --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-folder2-open me-2"></i>
                        Informacion del Expediente
                    </h6>
                    <a href="{{ route('inmigration-files.edit', $file->id) }}#info" class="btn btn-sm btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Codigo</label>
                            <p class="mb-2 fw-bold"><code class="fs-5">{{ $file->file_code }}</code></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Campaña</label>
                            <p class="mb-2"><span class="badge bg-primary">{{ $file->campaign }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Tipo Solicitud</label>
                            <p class="mb-2"><span class="badge bg-info">{{ $file->application_type->name }}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted mb-0">Titulo</label>
                            <p class="mb-2">{{ $file->file_title }}</p>
                        </div>
                    </div>

                    {{-- Desplegable Requisitos GENERAL --}}
                    @php $generalInfo = $getDropdownInfo($reqsByEntity['GENERAL']); @endphp
                    <div class="mt-3 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer"
                             wire:click="toggleSection('GENERAL')"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ in_array('GENERAL', $openSections) ? 'chevron-down' : 'chevron-right' }} me-2"></i>
                                <span class="fw-semibold">Requisitos General</span>
                                <span class="badge {{ $generalInfo['badgeClass'] }} ms-2">{{ $generalInfo['badgeText'] }}</span>
                                @if($reqsByEntity['GENERAL']['overdue'] > 0)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-2"></i>
                                @endif
                            </div>
                            <button wire:click.stop="startAddingRequirement('GENERAL')" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        @if(in_array('GENERAL', $openSections))
                            <div class="mt-2">
                                {{-- Formulario nuevo requisito --}}
                                @if($addingToEntity === 'GENERAL')
                                    <div class="p-2 bg-light rounded mb-2">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" wire:model="req_name" class="form-control form-control-sm @error('req_name') is-invalid @enderror" placeholder="Nombre del requisito *">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" wire:model="req_description" class="form-control form-control-sm" placeholder="Descripcion">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" wire:model="req_due_date" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model="req_is_mandatory" class="form-check-input" id="req_mandatory_general">
                                                    <label class="form-check-label small" for="req_mandatory_general">Oblig.</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model="req_save_as_template" class="form-check-input" id="req_template_general">
                                                    <label class="form-check-label small text-primary" for="req_template_general">
                                                        <i class="bi bi-bookmark-plus me-1"></i>Guardar como plantilla
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button wire:click="saveRequirement" class="btn btn-sm btn-success me-1">Guardar</button>
                                                <button wire:click="cancelAddingRequirement" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Lista de requisitos --}}
                                @if($reqsByEntity['GENERAL']['items']->count() > 0)
                                    @foreach($reqsByEntity['GENERAL']['items'] as $req)
                                        <div class="d-flex align-items-start py-1 border-bottom" wire:key="req-general-{{ $req->id }}">
                                            <button wire:click="toggleRequirement({{ $req->id }})" class="btn btn-sm p-0 me-2">
                                                <i class="bi bi-{{ $req->is_completed ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                            </button>
                                            <div class="flex-grow-1 small {{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $req->name }}
                                                @if($req->is_mandatory)<span class="badge bg-danger ms-1">Oblig.</span>@else<span class="badge bg-secondary ms-1">Opcional</span>@endif
                                                @if($req->due_date && !$req->is_completed && $req->due_date->isPast())<i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>@endif
                                            </div>
                                            @if(!$req->requirement_template_id && !$req->is_completed)
                                                <button wire:click="deleteRequirement({{ $req->id }})" wire:confirm="¿Eliminar?" class="btn btn-sm p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0 mt-2">Sin requisitos generales</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Datos Laborales --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-briefcase me-2"></i>
                        Datos Laborales
                    </h6>
                    <a href="{{ route('inmigration-files.edit', $file->id) }}#laboral" class="btn btn-sm btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Puesto de Trabajo</label>
                            <p class="mb-2 fw-semibold">{{ $file->job_title }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Salario</label>
                            <p class="mb-2">{{ $file->salary ? number_format($file->salary, 2, ',', '.') . ' EUR' : '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Fecha Inicio</label>
                            <p class="mb-2">{{ $file->start_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Fecha Fin</label>
                            <p class="mb-2">{{ $file->end_date?->format('d/m/Y') ?? 'Indefinido' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Tipo Jornada</label>
                            <p class="mb-2">{{ $file->working_day_type?->value ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Horas Semanales</label>
                            <p class="mb-2">{{ $file->working_hours ?? '-' }}</p>
                        </div>
                        @if($file->probation_period)
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-0">Periodo Prueba</label>
                                <p class="mb-2">{{ $file->probation_period }} dias</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Empleador --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-building me-2"></i>
                        Empleador
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inmigration-files.edit', $file->id) }}#empleador" class="btn btn-sm btn-outline-warning" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($file->employer)
                            <a href="{{ route('employers.show', $file->employer->id) }}" class="btn btn-sm btn-outline-primary">
                                Ver Empleador
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($file->employer)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Comercial</label>
                                <p class="mb-2 fw-semibold">{{ $file->employer->comercial_name }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-0">NIF</label>
                                <p class="mb-2"><code>{{ $file->employer->nif }}</code></p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-0">Forma Juridica</label>
                                <p class="mb-2"><span class="badge bg-secondary">{{ $file->employer->legal_form->name }}</span></p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin empleador asignado</p>
                    @endif

                    {{-- Desplegable Requisitos EMPLOYER --}}
                    @php $employerInfo = $getDropdownInfo($reqsByEntity['EMPLOYER']); @endphp
                    <div class="mt-3 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center"
                             wire:click="toggleSection('EMPLOYER')"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ in_array('EMPLOYER', $openSections) ? 'chevron-down' : 'chevron-right' }} me-2"></i>
                                <span class="fw-semibold">Requisitos Empleador</span>
                                <span class="badge {{ $employerInfo['badgeClass'] }} ms-2">{{ $employerInfo['badgeText'] }}</span>
                                @if($reqsByEntity['EMPLOYER']['overdue'] > 0)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-2"></i>
                                @endif
                            </div>
                            <button wire:click.stop="startAddingRequirement('EMPLOYER')" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        @if(in_array('EMPLOYER', $openSections))
                            <div class="mt-2">
                                @if($addingToEntity === 'EMPLOYER')
                                    <div class="p-2 bg-light rounded mb-2">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" wire:model="req_name" class="form-control form-control-sm @error('req_name') is-invalid @enderror" placeholder="Nombre del requisito *">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" wire:model="req_description" class="form-control form-control-sm" placeholder="Descripcion">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" wire:model="req_due_date" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model="req_is_mandatory" class="form-check-input" id="req_mandatory_employer">
                                                    <label class="form-check-label small" for="req_mandatory_employer">Oblig.</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model="req_save_as_template" class="form-check-input" id="req_template_employer">
                                                    <label class="form-check-label small text-primary" for="req_template_employer">
                                                        <i class="bi bi-bookmark-plus me-1"></i>Guardar como plantilla
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button wire:click="saveRequirement" class="btn btn-sm btn-success me-1">Guardar</button>
                                                <button wire:click="cancelAddingRequirement" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($reqsByEntity['EMPLOYER']['items']->count() > 0)
                                    @foreach($reqsByEntity['EMPLOYER']['items'] as $req)
                                        <div class="d-flex align-items-start py-1 border-bottom" wire:key="req-employer-{{ $req->id }}">
                                            <button wire:click="toggleRequirement({{ $req->id }})" class="btn btn-sm p-0 me-2">
                                                <i class="bi bi-{{ $req->is_completed ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                            </button>
                                            <div class="flex-grow-1 small {{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $req->name }}
                                                @if($req->is_mandatory)<span class="badge bg-danger ms-1">Oblig.</span>@else<span class="badge bg-secondary ms-1">Opcional</span>@endif
                                                @if($req->due_date && !$req->is_completed && $req->due_date->isPast())<i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>@endif
                                            </div>
                                            @if(!$req->requirement_template_id && !$req->is_completed)
                                                <button wire:click="deleteRequirement({{ $req->id }})" wire:confirm="¿Eliminar?" class="btn btn-sm p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0 mt-2">Sin requisitos de empleador</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Trabajador --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-person me-2"></i>
                        Trabajador Extranjero
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inmigration-files.edit', $file->id) }}#trabajador" class="btn btn-sm btn-outline-warning" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($file->foreigner)
                            <a href="{{ route('foreigners.show', $file->foreigner->id) }}" class="btn btn-sm btn-outline-primary">
                                Ver Extranjero
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($file->foreigner)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Completo</label>
                                <p class="mb-2 fw-semibold">{{ $file->foreigner->first_name }} {{ $file->foreigner->last_name }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-0">NIE</label>
                                <p class="mb-2"><code>{{ $file->foreigner->nie }}</code></p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-0">Pasaporte</label>
                                <p class="mb-2"><code>{{ $file->foreigner->passport }}</code></p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin trabajador asignado</p>
                    @endif

                    {{-- Desplegable Requisitos WORKER --}}
                    @php $workerInfo = $getDropdownInfo($reqsByEntity['WORKER']); @endphp
                    <div class="mt-3 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center"
                             wire:click="toggleSection('WORKER')"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ in_array('WORKER', $openSections) ? 'chevron-down' : 'chevron-right' }} me-2"></i>
                                <span class="fw-semibold">Requisitos Trabajador</span>
                                <span class="badge {{ $workerInfo['badgeClass'] }} ms-2">{{ $workerInfo['badgeText'] }}</span>
                                @if($reqsByEntity['WORKER']['overdue'] > 0)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-2"></i>
                                @endif
                            </div>
                            <button wire:click.stop="startAddingRequirement('WORKER')" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        @if(in_array('WORKER', $openSections))
                            <div class="mt-2">
                                @if($addingToEntity === 'WORKER')
                                    <div class="p-2 bg-light rounded mb-2">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" wire:model="req_name" class="form-control form-control-sm @error('req_name') is-invalid @enderror" placeholder="Nombre del requisito *">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" wire:model="req_description" class="form-control form-control-sm" placeholder="Descripcion">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" wire:model="req_due_date" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model="req_is_mandatory" class="form-check-input" id="req_mandatory_worker">
                                                    <label class="form-check-label small" for="req_mandatory_worker">Oblig.</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model="req_save_as_template" class="form-check-input" id="req_template_worker">
                                                    <label class="form-check-label small text-primary" for="req_template_worker">
                                                        <i class="bi bi-bookmark-plus me-1"></i>Guardar como plantilla
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button wire:click="saveRequirement" class="btn btn-sm btn-success me-1">Guardar</button>
                                                <button wire:click="cancelAddingRequirement" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($reqsByEntity['WORKER']['items']->count() > 0)
                                    @foreach($reqsByEntity['WORKER']['items'] as $req)
                                        <div class="d-flex align-items-start py-1 border-bottom" wire:key="req-worker-{{ $req->id }}">
                                            <button wire:click="toggleRequirement({{ $req->id }})" class="btn btn-sm p-0 me-2">
                                                <i class="bi bi-{{ $req->is_completed ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                            </button>
                                            <div class="flex-grow-1 small {{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $req->name }}
                                                @if($req->is_mandatory)<span class="badge bg-danger ms-1">Oblig.</span>@else<span class="badge bg-secondary ms-1">Opcional</span>@endif
                                                @if($req->due_date && !$req->is_completed && $req->due_date->isPast())<i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>@endif
                                            </div>
                                            @if(!$req->requirement_template_id && !$req->is_completed)
                                                <button wire:click="deleteRequirement({{ $req->id }})" wire:confirm="¿Eliminar?" class="btn btn-sm p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0 mt-2">Sin requisitos de trabajador</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Representante Legal --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Representante Legal
                    </h6>
                    <a href="{{ route('inmigration-files.edit', $file->id) }}#representante" class="btn btn-sm btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($file->editor)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Completo</label>
                                <p class="mb-2 fw-semibold">{{ $file->editor->first_name }} {{ $file->editor->last_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Legal</label>
                                <p class="mb-2">{{ $file->editor->legal_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">DNI</label>
                                <p class="mb-2"><code>{{ $file->editor->dni ?? '-' }}</code></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">Telefono</label>
                                <p class="mb-2">{{ $file->editor->phone_number ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-0">Email</label>
                                <p class="mb-2">{{ $file->editor->email }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin representante asignado</p>
                    @endif

                    {{-- Desplegable Requisitos REPRESENTATIVE --}}
                    @php $repInfo = $getDropdownInfo($reqsByEntity['REPRESENTATIVE']); @endphp
                    <div class="mt-3 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center"
                             wire:click="toggleSection('REPRESENTATIVE')"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-{{ in_array('REPRESENTATIVE', $openSections) ? 'chevron-down' : 'chevron-right' }} me-2"></i>
                                <span class="fw-semibold">Requisitos Representante</span>
                                <span class="badge {{ $repInfo['badgeClass'] }} ms-2">{{ $repInfo['badgeText'] }}</span>
                                @if($reqsByEntity['REPRESENTATIVE']['overdue'] > 0)
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-2"></i>
                                @endif
                            </div>
                            <button wire:click.stop="startAddingRequirement('REPRESENTATIVE')" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        @if(in_array('REPRESENTATIVE', $openSections))
                            <div class="mt-2">
                                @if($addingToEntity === 'REPRESENTATIVE')
                                    <div class="p-2 bg-light rounded mb-2">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" wire:model="req_name" class="form-control form-control-sm @error('req_name') is-invalid @enderror" placeholder="Nombre del requisito *">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" wire:model="req_description" class="form-control form-control-sm" placeholder="Descripcion">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" wire:model="req_due_date" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input type="checkbox" wire:model="req_is_mandatory" class="form-check-input" id="req_mandatory_rep">
                                                    <label class="form-check-label small" for="req_mandatory_rep">Oblig.</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" wire:model="req_save_as_template" class="form-check-input" id="req_template_rep">
                                                    <label class="form-check-label small text-primary" for="req_template_rep">
                                                        <i class="bi bi-bookmark-plus me-1"></i>Guardar como plantilla
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button wire:click="saveRequirement" class="btn btn-sm btn-success me-1">Guardar</button>
                                                <button wire:click="cancelAddingRequirement" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($reqsByEntity['REPRESENTATIVE']['items']->count() > 0)
                                    @foreach($reqsByEntity['REPRESENTATIVE']['items'] as $req)
                                        <div class="d-flex align-items-start py-1 border-bottom" wire:key="req-rep-{{ $req->id }}">
                                            <button wire:click="toggleRequirement({{ $req->id }})" class="btn btn-sm p-0 me-2">
                                                <i class="bi bi-{{ $req->is_completed ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                            </button>
                                            <div class="flex-grow-1 small {{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $req->name }}
                                                @if($req->is_mandatory)<span class="badge bg-danger ms-1">Oblig.</span>@else<span class="badge bg-secondary ms-1">Opcional</span>@endif
                                                @if($req->due_date && !$req->is_completed && $req->due_date->isPast())<i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>@endif
                                            </div>
                                            @if(!$req->requirement_template_id && !$req->is_completed)
                                                <button wire:click="deleteRequirement({{ $req->id }})" wire:confirm="¿Eliminar?" class="btn btn-sm p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0 mt-2">Sin requisitos de representante</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Gestion de Estado --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Gestion de Estado
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Estado Actual --}}
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Estado Actual</label>
                        <div>
                            <span class="badge bg-{{ $statusColors[$file->status->value] ?? 'secondary' }} fs-6">
                                {{ $file->status->label() }}
                            </span>
                        </div>
                    </div>

                    {{-- Cambiar Estado --}}
                    @if(count($availableTransitions) > 0)
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-2">Cambiar a</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($availableTransitions as $transition)
                                    <button wire:click="changeStatus('{{ $transition->value }}')"
                                            class="btn btn-sm btn-outline-{{ $statusColors[$transition->value] ?? 'secondary' }}">
                                        {{ $transition->label() }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-3">
                            <i class="bi bi-lock me-1"></i>
                            Sin transiciones disponibles
                        </p>
                    @endif

                    {{-- Requisitos sin entidad (generales del estado) --}}
                    @php
                        $reqsSinEntidad = $file->requirements->filter(fn($r) => $r->target_entity === null);
                        $reqsSinEntidadPendientes = $reqsSinEntidad->where('is_completed', false);
                    @endphp
                    @if($reqsSinEntidad->count() > 0)
                        <div class="border-top pt-3">
                            <label class="form-label small text-muted mb-2">
                                Requisitos del Estado
                                <span class="badge bg-{{ $reqsSinEntidadPendientes->count() > 0 ? 'warning' : 'success' }} ms-1">
                                    {{ $reqsSinEntidad->where('is_completed', true)->count() }}/{{ $reqsSinEntidad->count() }}
                                </span>
                            </label>
                            <div class="list-group list-group-flush">
                                @foreach($reqsSinEntidad as $req)
                                    <div class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                        <button wire:click="toggleRequirement({{ $req->id }})" class="btn btn-sm p-0 me-2">
                                            <i class="bi bi-{{ $req->is_completed ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                        </button>
                                        <span class="small {{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $req->name }}
                                        </span>
                                        @if($req->is_mandatory)
                                            <span class="badge bg-danger ms-auto">Oblig.</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Acciones Rapidas --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($file->employer_id && $file->foreigner_id)
                            <a href="{{ route('documents.generate', $file->id) }}" class="btn btn-primary">
                                <i class="bi bi-file-earmark-zip me-1"></i>
                                Generar Pack Documentos
                            </a>
                            <a href="{{ route('documents.generate-ex', $file->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf me-1"></i>
                                Solo Modelo EX
                            </a>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="bi bi-file-earmark-pdf me-1"></i>
                                Generar Documentos
                            </button>
                            <small class="text-muted text-center">Falta empleador o trabajador</small>
                        @endif
                        <hr class="my-2">
                        <a href="{{ route('checklist.index', $file->id) }}" class="btn btn-outline-info">
                            <i class="bi bi-list-check me-1"></i>
                            Ver Checklist
                        </a>
                        <hr class="my-2">
                        <button wire:click="delete"
                                wire:confirm="¿Estas seguro de eliminar este expediente? Esta accion no se puede deshacer."
                                class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>
                            Eliminar Expediente
                        </button>
                    </div>
                </div>
            </div>

            {{-- Direccion Trabajo --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-geo-alt me-2"></i>
                        Lugar de Trabajo
                    </h6>
                </div>
                <div class="card-body">
                    @if($file->workAddress)
                        <p class="mb-1">{{ $file->workAddress->street_name }} {{ $file->workAddress->number }}</p>
                        @if($file->workAddress->floor_door)
                            <p class="mb-1">{{ $file->workAddress->floor_door }}</p>
                        @endif
                        <p class="mb-1">{{ $file->workAddress->postal_code }} {{ $file->workAddress->municipality?->municipality_name }}</p>
                        <p class="mb-0">{{ $file->workAddress->province?->province_name }}, {{ $file->workAddress->country?->country_name }}</p>
                    @else
                        <p class="text-muted mb-0">Sin direccion registrada</p>
                    @endif
                </div>
            </div>

            {{-- Metadatos --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informacion
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Creado</label>
                        <p class="mb-0 small">{{ $file->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Actualizado</label>
                        <p class="mb-0 small">{{ $file->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
