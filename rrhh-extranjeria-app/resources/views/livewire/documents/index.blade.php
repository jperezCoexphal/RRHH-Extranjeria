<?php

use App\Models\InmigrationFile;
use App\Enums\ImmigrationFileStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Documentos - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $campaign = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'campaign']);
        $this->resetPage();
    }

    public function with(): array
    {
        $query = InmigrationFile::with(['employer', 'foreigner'])
            ->whereIn('status', ['listo', 'presentado', 'favorable']);

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

        if ($this->campaign) {
            $query->where('campaign', $this->campaign);
        }

        $campaigns = InmigrationFile::select('campaign')->distinct()->orderBy('campaign', 'desc')->pluck('campaign');

        return [
            'files' => $query->orderBy('updated_at', 'desc')->paginate(10),
            'statuses' => [
                ImmigrationFileStatus::LISTO,
                ImmigrationFileStatus::PRESENTADO,
                ImmigrationFileStatus::FAVORABLE,
            ],
            'campaigns' => $campaigns,
        ];
    }
}; ?>

<div>
    @section('page-title', 'Documentos')

    @php
        $statusColors = [
            'listo' => 'info',
            'presentado' => 'primary',
            'favorable' => 'success',
        ];
    @endphp

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Generacion de Documentos</h4>
            <p class="text-muted mb-0">Gestiona los documentos de los expedientes listos para presentar</p>
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
                <div class="col-md-4">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Codigo, titulo, empleador...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Estado</label>
                    <select wire:model.live="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Campana</label>
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
                            <th>Expediente</th>
                            <th>Empleador</th>
                            <th>Trabajador</th>
                            <th>Estado</th>
                            <th>Documentos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                            <tr wire:key="file-{{ $file->id }}">
                                <td>
                                    <a href="{{ route('documents.show', $file->id) }}" wire:navigate class="text-decoration-none fw-bold text-primary">
                                        {{ $file->file_code }}
                                    </a>
                                    <br><small class="text-muted">{{ $file->campaign }}</small>
                                </td>
                                <td>
                                    @if($file->employer)
                                        {{ Str::limit($file->employer->comercial_name, 25) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($file->foreigner)
                                        {{ $file->foreigner->first_name }} {{ Str::limit($file->foreigner->last_name, 15) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$file->status->value] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $file->status->value)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        Pendiente
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('documents.show', $file->id) }}" wire:navigate class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        Gestionar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-file-earmark-x text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No hay expedientes listos para generar documentos</p>
                                    <small class="text-muted">Los expedientes deben estar en estado "Listo" o superior</small>
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
