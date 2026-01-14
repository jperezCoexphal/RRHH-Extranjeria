<?php

use App\Models\Foreigner;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Detalle Extranjero - RRHH Extranjeria')]
class extends Component {
    public Foreigner $foreigner;

    public function mount(Foreigner $foreigner): void
    {
        $this->foreigner = $foreigner->load([
            'extraData',
            'nationality',
            'birthCountry',
            'address.country',
            'address.province',
            'address.municipality',
            'inmigrationFiles.employer'
        ]);
    }

    public function delete(): void
    {
        $this->foreigner->delete();
        session()->flash('success', 'Extranjero eliminado correctamente.');
        $this->redirect(route('foreigners.index'), navigate: true);
    }
}; ?>

<div>
    @section('page-title', 'Detalle Extranjero')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('foreigners.index') }}">Extranjeros</a></li>
                    <li class="breadcrumb-item active">{{ $foreigner->first_name }} {{ $foreigner->last_name }}</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">{{ $foreigner->first_name }} {{ $foreigner->last_name }}</h4>
        </div>
        <div class="btn-group">
            <a href="{{ route('foreigners.edit', $foreigner->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>
                Editar
            </a>
            <button wire:click="delete"
                    wire:confirm="¿Estas seguro de eliminar este extranjero?"
                    class="btn btn-danger">
                <i class="bi bi-trash me-1"></i>
                Eliminar
            </button>
        </div>
    </div>

    <div class="row">
        {{-- Datos Principales --}}
        <div class="col-lg-8">
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
                            <label class="form-label small text-muted mb-0">Nombre</label>
                            <p class="mb-2 fw-semibold">{{ $foreigner->first_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Apellidos</label>
                            <p class="mb-2 fw-semibold">{{ $foreigner->last_name }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">NIE</label>
                            <p class="mb-2"><code class="fs-6">{{ $foreigner->nie }}</code></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Pasaporte</label>
                            <p class="mb-2"><code class="fs-6">{{ $foreigner->passport }}</code></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">NISS</label>
                            <p class="mb-2">{{ $foreigner->niss ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Genero</label>
                            <p class="mb-2">
                                <i class="bi bi-{{ $foreigner->gender->value === 'Masculino' ? 'gender-male text-primary' : 'gender-female text-danger' }} me-1"></i>
                                {{ $foreigner->gender->value }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Fecha Nacimiento</label>
                            <p class="mb-2">{{ $foreigner->birthdate?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Estado Civil</label>
                            <p class="mb-2">{{ $foreigner->marital_status->value }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Nacionalidad</label>
                            <p class="mb-2"><span class="badge bg-primary">{{ $foreigner->nationality?->country_name ?? '-' }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Pais Nacimiento</label>
                            <p class="mb-2">{{ $foreigner->birthCountry?->country_name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Lugar Nacimiento</label>
                            <p class="mb-2">{{ $foreigner->birthplace_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos Adicionales --}}
            @if($foreigner->extraData)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-card-list me-2"></i>
                            Datos Adicionales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre del Padre</label>
                                <p class="mb-2">{{ $foreigner->extraData->father_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre de la Madre</label>
                                <p class="mb-2">{{ $foreigner->extraData->mother_name ?? '-' }}</p>
                            </div>
                            @if($foreigner->extraData->legal_guardian_name)
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-0">Tutor Legal</label>
                                    <p class="mb-2">{{ $foreigner->extraData->legal_guardian_name }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-0">Doc. Identidad Tutor</label>
                                    <p class="mb-2">{{ $foreigner->extraData->legal_guardian_identity_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-0">Titulo Tutela</label>
                                    <p class="mb-2">{{ $foreigner->extraData->guardianship_title ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Expedientes Asociados --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-folder2-open me-2"></i>
                        Expedientes Asociados
                    </h6>
                    <span class="badge bg-primary">{{ $foreigner->inmigrationFiles->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($foreigner->inmigrationFiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Empleador</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($foreigner->inmigrationFiles as $file)
                                        <tr>
                                            <td>
                                                <a href="{{ route('inmigration-files.show', $file->id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $file->file_code }}
                                                </a>
                                            </td>
                                            <td>{{ $file->employer?->comercial_name ?? '-' }}</td>
                                            <td>
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
                                                <span class="badge bg-{{ $statusColors[$file->status->value] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $file->status->value)) }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">{{ $file->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-folder text-gray-300" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Sin expedientes asociados</p>
                        </div>
                    @endif
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
                        <label class="form-label small text-muted mb-0">Email</label>
                        @if($foreigner->extraData?->email)
                            <p class="mb-0"><a href="mailto:{{ $foreigner->extraData->email }}">{{ $foreigner->extraData->email }}</a></p>
                        @else
                            <p class="mb-0 text-muted">-</p>
                        @endif
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Telefono</label>
                        @if($foreigner->extraData?->phone)
                            <p class="mb-0"><a href="tel:{{ $foreigner->extraData->phone }}">{{ $foreigner->extraData->phone }}</a></p>
                        @else
                            <p class="mb-0 text-muted">-</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Direccion --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-geo-alt me-2"></i>
                        Direccion
                    </h6>
                </div>
                <div class="card-body">
                    @if($foreigner->address)
                        <p class="mb-1">{{ $foreigner->address->street_name }} {{ $foreigner->address->number }}</p>
                        @if($foreigner->address->floor_door)
                            <p class="mb-1">{{ $foreigner->address->floor_door }}</p>
                        @endif
                        <p class="mb-1">{{ $foreigner->address->postal_code }} {{ $foreigner->address->municipality?->municipality_name }}</p>
                        <p class="mb-0">{{ $foreigner->address->province?->province_name }}, {{ $foreigner->address->country?->country_name }}</p>
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
                        <p class="mb-0 small">{{ $foreigner->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Actualizado</label>
                        <p class="mb-0 small">{{ $foreigner->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
