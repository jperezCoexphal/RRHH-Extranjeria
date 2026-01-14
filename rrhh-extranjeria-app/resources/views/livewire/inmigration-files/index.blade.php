<?php

use App\Models\InmigrationFile;
use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Helpers\CampaignHelper;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Expedientes - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $applicationType = '';

    #[Url]
    public string $campaign = '';

    public function mount(): void
    {
        // Establecer Campaña actual por defecto si no viene en la URL
        if (empty($this->campaign) && !request()->has('campaign')) {
            $this->campaign = CampaignHelper::current();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->campaign = CampaignHelper::current();
        $this->reset(['search', 'status', 'applicationType']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $file = InmigrationFile::find($id);
        if ($file) {
            $file->delete();
            session()->flash('success', 'Expediente eliminado correctamente.');
        }
    }

    public function with(): array
    {
        $query = InmigrationFile::with(['employer', 'foreigner', 'editor']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('file_code', 'like', "%{$this->search}%")
                  ->orWhere('file_title', 'like', "%{$this->search}%")
                  ->orWhereHas('employer', fn($q) => $q->where('comercial_name', 'like', "%{$this->search}%"))
                  ->orWhereHas('foreigner', fn($q) => $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->applicationType) {
            $query->where('application_type', $this->applicationType);
        }

        if ($this->campaign) {
            $query->where('campaign', $this->campaign);
        }

        // Obtener Campañas unicas y formatearlas
        $campaigns = InmigrationFile::select('campaign')
            ->distinct()
            ->orderBy('campaign', 'desc')
            ->pluck('campaign')
            ->map(fn($c) => CampaignHelper::format($c))
            ->unique()
            ->values()
            ->toArray();

        // Asegurar que la Campaña actual siempre este en la lista
        $currentCampaign = CampaignHelper::current();
        if (!in_array($currentCampaign, $campaigns)) {
            array_unshift($campaigns, $currentCampaign);
        }

        return [
            'files' => $query->orderBy('created_at', 'desc')->paginate(10),
            'statuses' => ImmigrationFileStatus::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'campaigns' => $campaigns,
        ];
    }
}; ?>

<div>
    @section('page-title', 'Expedientes')

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
    @endphp

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Gestion de Expedientes</h4>
            <p class="text-muted mb-0">Expedientes de inmigracion</p>
        </div>
        <a href="{{ route('inmigration-files.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo Expediente
        </a>
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
                               placeholder="Codigo, titulo, empleador...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Estado</label>
                    <select wire:model.live="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tipo Solicitud</label>
                    <select wire:model.live="applicationType" class="form-select">
                        <option value="">Todos</option>
                        @foreach($applicationTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Campaña</label>
                    <select wire:model.live="campaign" class="form-select">
                        <option value="">Todas</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>
                        Limpiar
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
                            <th>Codigo</th>
                            <th>Titulo</th>
                            <th>Empleador</th>
                            <th>Trabajador</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                            <tr wire:key="file-{{ $file->id }}">
                                <td>
                                    <a href="{{ route('inmigration-files.show', $file->id) }}" class="text-decoration-none fw-bold text-primary">
                                        {{ $file->file_code }}
                                    </a>
                                    <br><small class="text-muted">{{ \App\Helpers\CampaignHelper::format($file->campaign) }}</small>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $file->file_title }}">
                                        {{ $file->file_title }}
                                    </span>
                                </td>
                                <td>
                                    @if($file->employer)
                                        <a href="{{ route('employers.show', $file->employer->id) }}" class="text-decoration-none">
                                            {{ Str::limit($file->employer->comercial_name, 25) }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($file->foreigner)
                                        <a href="{{ route('foreigners.show', $file->foreigner->id) }}" class="text-decoration-none">
                                            {{ $file->foreigner->first_name }} {{ Str::limit($file->foreigner->last_name, 15) }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="badge bg-light text-dark">{{ $file->application_type->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$file->status->value] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $file->status->value)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('inmigration-files.show', $file->id) }}" class="btn btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('inmigration-files.edit', $file->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="delete({{ $file->id }})"
                                                wire:confirm="¿Estas seguro de eliminar este expediente?"
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
                                    <i class="bi bi-folder2-open text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No se encontraron expedientes</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($files->hasPages())
            <div class="card-footer">
                {{ $files->links() }}
            </div>
        @endif
    </div>
</div>
