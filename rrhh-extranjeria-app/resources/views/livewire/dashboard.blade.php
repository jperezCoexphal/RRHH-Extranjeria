<?php

use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\InmigrationFile;
use App\Helpers\CampaignHelper;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

new
#[Layout('layouts.app')]
#[Title('Dashboard - RRHH Extranjeria')]
class extends Component {
    #[Url]
    public string $campaign = '';

    public int $totalExpedientes = 0;
    public int $totalEmpleadores = 0;
    public int $totalExtranjeros = 0;
    public int $expedientesPendientes = 0;
    public int $expedientesFavorables = 0;
    public int $expedientesDenegados = 0;

    public array $expedientesPorEstado = [];
    public array $expedientesRecientes = [];
    public array $campaigns = [];

    public function mount(): void
    {
        // Establecer Campaña actual por defecto si no viene en la URL
        if (empty($this->campaign)) {
            $this->campaign = CampaignHelper::current();
        }

        $this->loadCampaigns();
        $this->loadStats();
    }

    public function updatedCampaign(): void
    {
        $this->loadStats();
    }

    public function loadCampaigns(): void
    {
        $this->campaigns = InmigrationFile::select('campaign')
            ->distinct()
            ->orderBy('campaign', 'desc')
            ->pluck('campaign')
            ->map(fn($c) => CampaignHelper::format($c))
            ->unique()
            ->values()
            ->toArray();

        // Asegurar que la Campaña actual siempre este en la lista
        $currentCampaign = CampaignHelper::current();
        if (!in_array($currentCampaign, $this->campaigns)) {
            array_unshift($this->campaigns, $currentCampaign);
        }
    }

    public function loadStats(): void
    {
        // Query base filtrada por Campaña
        $baseQuery = InmigrationFile::where('campaign', $this->campaign);

        // Contadores principales
        $this->totalExpedientes = (clone $baseQuery)->count();
        $this->totalEmpleadores = Employer::count();
        $this->totalExtranjeros = Foreigner::count();

        // Expedientes por estado
        $this->expedientesPendientes = (clone $baseQuery)->where('status', 'pendiente_revision')->count();
        $this->expedientesFavorables = (clone $baseQuery)->where('status', 'favorable')->count();
        $this->expedientesDenegados = (clone $baseQuery)->where('status', 'denegado')->count();

        // Expedientes por estado para grafico
        $this->expedientesPorEstado = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Expedientes recientes
        $this->expedientesRecientes = (clone $baseQuery)
            ->with(['employer', 'foreigner'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function refresh(): void
    {
        $this->loadCampaigns();
        $this->loadStats();
    }
}; ?>

<div>
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Panel de Control</h4>
            <p class="text-muted mb-0">Resumen general de la aplicacion</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-muted small">Campaña:</label>
                <select wire:model.live="campaign" class="form-select form-select-sm" style="width: auto;">
                    @foreach($campaigns as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="refresh" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Actualizar
            </button>
        </div>
    </div>

    {{-- Stat Cards Row --}}
    <div class="row">
        {{-- Total Expedientes --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-primary h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label text-primary">Total Expedientes</div>
                            <div class="stat-value">{{ $totalExpedientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-folder2-open stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Empleadores --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-success h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label text-success">Empleadores</div>
                            <div class="stat-value">{{ $totalEmpleadores }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-building stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Extranjeros --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-info h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label text-info">Extranjeros</div>
                            <div class="stat-value">{{ $totalExtranjeros }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pendientes de Revision --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-warning h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label text-warning">Pendientes</div>
                            <div class="stat-value">{{ $expedientesPendientes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row --}}
    <div class="row">
        {{-- Estado de Expedientes --}}
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Estado de Expedientes
                    </h6>
                </div>
                <div class="card-body">
                    @if(count($expedientesPorEstado) > 0)
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
                            $statusLabels = [
                                'borrador' => 'Borrador',
                                'pendiente_revision' => 'Pendiente',
                                'listo' => 'Listo',
                                'presentado' => 'Presentado',
                                'requerido' => 'Requerido',
                                'favorable' => 'Favorable',
                                'denegado' => 'Denegado',
                                'archivado' => 'Archivado',
                            ];
                        @endphp
                        @foreach($expedientesPorEstado as $estado => $cantidad)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-gray-600">{{ $statusLabels[$estado] ?? ucfirst($estado) }}</span>
                                    <span class="small fw-bold">{{ $cantidad }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $statusColors[$estado] ?? 'primary' }}"
                                         role="progressbar"
                                         style="width: {{ $totalExpedientes > 0 ? ($cantidad / $totalExpedientes) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-gray-300" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No hay expedientes registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Expedientes Recientes --}}
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Expedientes Recientes
                    </h6>
                    <a href="{{ route('inmigration-files.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todos
                    </a>
                </div>
                <div class="card-body p-0">
                    @if(count($expedientesRecientes) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Titulo</th>
                                        <th>Empleador</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expedientesRecientes as $expediente)
                                        <tr>
                                            <td>
                                                <a href="{{ route('inmigration-files.show', $expediente['id']) }}" class="fw-bold text-primary text-decoration-none">
                                                    {{ $expediente['file_code'] }}
                                                </a>
                                            </td>
                                            <td class="text-truncate" style="max-width: 200px;">
                                                {{ $expediente['file_title'] }}
                                            </td>
                                            <td>
                                                {{ $expediente['employer']['comercial_name'] ?? $expediente['employer']['fiscal_name'] ?? 'N/A' }}
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = $statusColors[$expediente['status']] ?? 'primary';
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ $statusLabels[$expediente['status']] ?? ucfirst($expediente['status']) }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ \Carbon\Carbon::parse($expediente['created_at'])->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-folder-x text-gray-300" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No hay expedientes recientes</p>
                            <a href="{{ route('inmigration-files.create') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-lg me-1"></i>
                                Crear Expediente
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones Rapidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('inmigration-files.create') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-folder-plus d-block mb-2" style="font-size: 1.5rem;"></i>
                                Nuevo Expediente
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('employers.create') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-building-add d-block mb-2" style="font-size: 1.5rem;"></i>
                                Nuevo Empleador
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('foreigners.create') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-person-plus d-block mb-2" style="font-size: 1.5rem;"></i>
                                Nuevo Extranjero
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="#" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-file-earmark-arrow-down d-block mb-2" style="font-size: 1.5rem;"></i>
                                Generar Informe
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row">
        {{-- Favorables --}}
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $expedientesFavorables }}</h3>
                            <span class="text-muted">Expedientes Favorables</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Denegados --}}
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="bi bi-x-circle text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $expedientesDenegados }}</h3>
                            <span class="text-muted">Expedientes Denegados</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tasa de Exito --}}
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-graph-up-arrow text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            @php
                                $totalResueltos = $expedientesFavorables + $expedientesDenegados;
                                $tasaExito = $totalResueltos > 0
                                    ? round(($expedientesFavorables / $totalResueltos) * 100, 1)
                                    : 0;
                            @endphp
                            <h3 class="mb-0">{{ $tasaExito }}%</h3>
                            <span class="text-muted">Tasa de Exito</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
