<?php

use App\Models\PdfTemplate;
use App\Enums\PdfTemplateType;
use App\Enums\ApplicationType;
use App\Services\PdfTemplateService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Plantillas PDF - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $documentType = '';

    #[Url]
    public string $applicationType = '';

    #[Url]
    public string $isActive = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDocumentType(): void
    {
        $this->resetPage();
    }

    public function updatingApplicationType(): void
    {
        $this->resetPage();
    }

    public function updatingIsActive(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'documentType', 'applicationType', 'isActive']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $template = PdfTemplate::find($id);
        if ($template) {
            $template->delete();
            session()->flash('success', 'Plantilla eliminada correctamente.');
        }
    }

    public function duplicate(int $id): void
    {
        $service = app(PdfTemplateService::class);
        $newTemplate = $service->duplicate($id);

        if ($newTemplate) {
            session()->flash('success', 'Plantilla duplicada correctamente.');
        } else {
            session()->flash('error', 'No se pudo duplicar la plantilla.');
        }
    }

    public function setAsDefault(int $id): void
    {
        $service = app(PdfTemplateService::class);
        if ($service->setAsDefault($id)) {
            session()->flash('success', 'Plantilla establecida como predeterminada.');
        }
    }

    public function toggleActive(int $id): void
    {
        $template = PdfTemplate::find($id);
        if ($template) {
            $template->update(['is_active' => !$template->is_active]);
            session()->flash('success', $template->is_active ? 'Plantilla activada.' : 'Plantilla desactivada.');
        }
    }

    public function with(): array
    {
        $query = PdfTemplate::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('original_filename', 'like', "%{$this->search}%");
            });
        }

        if ($this->documentType) {
            $query->where('document_type', $this->documentType);
        }

        if ($this->applicationType) {
            $query->where('application_type', $this->applicationType);
        }

        if ($this->isActive !== '') {
            $query->where('is_active', $this->isActive === '1');
        }

        return [
            'templates' => $query->orderBy('document_type')->orderBy('name')->paginate(15),
            'documentTypes' => PdfTemplateType::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'stats' => [
                'total' => PdfTemplate::count(),
                'active' => PdfTemplate::where('is_active', true)->count(),
                'defaults' => PdfTemplate::where('is_default', true)->count(),
                'byType' => PdfTemplate::selectRaw('document_type, count(*) as count')
                    ->groupBy('document_type')
                    ->pluck('count', 'document_type')
                    ->toArray(),
            ],
        ];
    }
}; ?>

<div>
    @section('page-title', 'Plantillas PDF')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Plantillas PDF</h4>
            <p class="text-muted mb-0">Gestiona las plantillas de formularios PDF editables (AcroForms)</p>
        </div>
        <a href="{{ route('pdf-templates.create') }}" class="btn btn-primary">
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
                        <i class="bi bi-file-earmark-pdf text-primary" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Activas</p>
                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Predeterminadas</p>
                            <h3 class="mb-0">{{ $stats['defaults'] }}</h3>
                        </div>
                        <i class="bi bi-star text-warning" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-0">Tipos de Documento</p>
                            <h3 class="mb-0">{{ count($stats['byType']) }}</h3>
                        </div>
                        <i class="bi bi-folder text-info" style="font-size: 2rem; opacity: 0.5;"></i>
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
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tipo de Documento</label>
                    <select wire:model.live="documentType" class="form-select">
                        <option value="">Todos</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tipo de Solicitud</label>
                    <select wire:model.live="applicationType" class="form-select">
                        <option value="">Todos</option>
                        @foreach($applicationTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Estado</label>
                    <select wire:model.live="isActive" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activas</option>
                        <option value="0">Inactivas</option>
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
                            <th>Tipo</th>
                            <th>Solicitud</th>
                            <th>Campos</th>
                            <th>Mapeo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr wire:key="template-{{ $template->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi {{ $template->document_type->icon() }} text-{{ $template->document_type->badgeColor() }} me-2" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <span class="fw-semibold">{{ $template->name }}</span>
                                            @if($template->is_default)
                                                <i class="bi bi-star-fill text-warning ms-1" title="Predeterminada"></i>
                                            @endif
                                            @if($template->description)
                                                <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $template->document_type->badgeColor() }}">
                                        {{ $template->document_type->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if($template->application_type)
                                        <span class="badge bg-secondary">{{ $template->application_type->name }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">General</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $template->total_fields }} campos
                                    </span>
                                </td>
                                <td>
                                    @if($template->total_fields > 0)
                                        <div class="progress" style="width: 80px; height: 6px;">
                                            <div class="progress-bar bg-{{ $template->mapping_progress == 100 ? 'success' : ($template->mapping_progress > 50 ? 'warning' : 'danger') }}"
                                                 style="width: {{ $template->mapping_progress }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $template->mapping_progress }}%</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pdf-templates.show', $template->id) }}" class="btn btn-outline-primary" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pdf-templates.edit', $template->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="duplicate({{ $template->id }})"
                                                class="btn btn-outline-info"
                                                title="Duplicar">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                        @if(!$template->is_default)
                                            <button wire:click="setAsDefault({{ $template->id }})"
                                                    class="btn btn-outline-warning"
                                                    title="Establecer como predeterminada">
                                                <i class="bi bi-star"></i>
                                            </button>
                                        @endif
                                        <button wire:click="toggleActive({{ $template->id }})"
                                                class="btn btn-outline-{{ $template->is_active ? 'secondary' : 'success' }}"
                                                title="{{ $template->is_active ? 'Desactivar' : 'Activar' }}">
                                            <i class="bi bi-{{ $template->is_active ? 'pause' : 'play' }}"></i>
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
                                    <i class="bi bi-file-earmark-pdf text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No se encontraron plantillas PDF</p>
                                    <a href="{{ route('pdf-templates.create') }}" class="btn btn-primary mt-3">
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
