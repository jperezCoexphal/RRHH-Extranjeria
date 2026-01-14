<?php

use App\Models\RequirementTemplate;
use App\Enums\TargetEntity;
use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Plantillas de Requisitos - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $targetEntity = '';

    #[Url]
    public string $applicationType = '';

    #[Url]
    public string $triggerStatus = '';

    #[Url]
    public string $isMandatory = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTargetEntity(): void
    {
        $this->resetPage();
    }

    public function updatingApplicationType(): void
    {
        $this->resetPage();
    }

    public function updatingTriggerStatus(): void
    {
        $this->resetPage();
    }

    public function updatingIsMandatory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'targetEntity', 'applicationType', 'triggerStatus', 'isMandatory']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $template = RequirementTemplate::find($id);
        if ($template) {
            $template->delete();
            session()->flash('success', 'Plantilla eliminada correctamente.');
        }
    }

    public function duplicate(int $id): void
    {
        $template = RequirementTemplate::find($id);
        if ($template) {
            $newTemplate = $template->replicate();
            $newTemplate->name = $template->name . ' (Copia)';
            $newTemplate->save();
            session()->flash('success', 'Plantilla duplicada correctamente.');
        }
    }

    public function with(): array
    {
        $query = RequirementTemplate::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->targetEntity) {
            $query->where('target_entity', $this->targetEntity);
        }

        if ($this->applicationType) {
            $query->where('application_type', $this->applicationType);
        }

        if ($this->triggerStatus) {
            $query->where('trigger_status', $this->triggerStatus);
        }

        if ($this->isMandatory !== '') {
            $query->where('is_mandatory', $this->isMandatory === '1');
        }

        return [
            'templates' => $query->orderBy('target_entity')->orderBy('name')->paginate(15),
            'targetEntities' => TargetEntity::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'statuses' => ImmigrationFileStatus::cases(),
            'stats' => [
                'total' => RequirementTemplate::count(),
                'mandatory' => RequirementTemplate::where('is_mandatory', true)->count(),
                'byEntity' => RequirementTemplate::selectRaw('target_entity, count(*) as count')
                    ->groupBy('target_entity')
                    ->pluck('count', 'target_entity')
                    ->toArray(),
            ],
        ];
    }
}; ?>

<div>
    @section('page-title', 'Plantillas de Requisitos')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Plantillas de Requisitos</h4>
            <p class="text-muted mb-0">Gestiona los requisitos que se generan automaticamente en los expedientes</p>
        </div>
        <a href="{{ route('requirement-templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nueva Plantilla
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Total Plantillas</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="bi bi-clipboard-check text-primary" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Obligatorios</p>
                            <h3 class="mb-0">{{ $stats['mandatory'] }}</h3>
                        </div>
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Opcionales</p>
                            <h3 class="mb-0">{{ $stats['total'] - $stats['mandatory'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Entidades</p>
                            <h3 class="mb-0">{{ count($stats['byEntity']) }}</h3>
                        </div>
                        <i class="bi bi-diagram-3 text-info" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0">
                <i class="bi bi-funnel me-2"></i>
                Filtros
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Nombre, descripcion...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Entidad</label>
                    <select wire:model.live="targetEntity" class="form-select">
                        <option value="">Todas</option>
                        @foreach($targetEntities as $entity)
                            <option value="{{ $entity->value }}">{{ $entity->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tipo Solicitud</label>
                    <select wire:model.live="applicationType" class="form-select">
                        <option value="">Todos</option>
                        @foreach($applicationTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Estado Activador</label>
                    <select wire:model.live="triggerStatus" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Obligatorio</label>
                    <select wire:model.live="isMandatory" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Si</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Entidad</th>
                            <th>Tipo Solicitud</th>
                            <th>Estado Activador</th>
                            <th>Dias Venc.</th>
                            <th>Tipo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr wire:key="template-{{ $template->id }}">
                                <td>
                                    <span class="fw-semibold">{{ $template->name }}</span>
                                    @if($template->description)
                                        <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($template->target_entity)
                                        <span class="badge bg-info">{{ $template->target_entity->label() }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->application_type)
                                        <span class="badge bg-secondary">{{ $template->application_type->name }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">Todos</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->trigger_status)
                                        <span class="badge bg-primary">{{ $template->trigger_status->label() }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->days_to_expire)
                                        <span class="badge bg-warning text-dark">{{ $template->days_to_expire }}d</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->is_mandatory)
                                        <span class="badge bg-danger">Oblig.</span>
                                    @else
                                        <span class="badge bg-secondary">Opcional</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('requirement-templates.edit', $template->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="duplicate({{ $template->id }})"
                                                class="btn btn-outline-info"
                                                title="Duplicar">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                        <button wire:click="delete({{ $template->id }})"
                                                wire:confirm="¿Estas seguro de eliminar esta plantilla?"
                                                class="btn btn-outline-danger"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-clipboard-x text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No se encontraron plantillas de requisitos</p>
                                    <a href="{{ route('requirement-templates.create') }}" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Crear primera plantilla
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($templates->hasPages())
            <div class="card-footer">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>
