<?php

use App\Models\Employer;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Detalle Empleador - RRHH Extranjeria')]
class extends Component {
    public Employer $employer;

    public function mount(Employer $employer): void
    {
        $this->employer = $employer->load(['company', 'freelancer', 'address.country', 'address.province', 'address.municipality', 'inmigrationFiles']);
    }

    public function delete(): void
    {
        $this->employer->delete();
        session()->flash('success', 'Empleador eliminado correctamente.');
        $this->redirect(route('employers.index'), navigate: true);
    }
}; ?>

<div>
    @section('page-title', 'Detalle Empleador')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('employers.index') }}">Empleadores</a></li>
                    <li class="breadcrumb-item active">{{ $employer->comercial_name }}</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">{{ $employer->comercial_name }}</h4>
        </div>
        <div class="btn-group">
            <a href="{{ route('employers.edit', $employer->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>
                Editar
            </a>
            <button wire:click="delete"
                    wire:confirm="¿Estas seguro de eliminar este empleador?"
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
                        <i class="bi bi-building me-2"></i>
                        Informacion General
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Nombre Comercial</label>
                            <p class="mb-2 fw-semibold">{{ $employer->comercial_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Nombre Fiscal</label>
                            <p class="mb-2">{{ $employer->fiscal_name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">NIF</label>
                            <p class="mb-2"><code class="fs-6">{{ $employer->nif }}</code></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Forma Juridica</label>
                            <p class="mb-2"><span class="badge bg-primary">{{ $employer->legal_form->name }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Asociado</label>
                            <p class="mb-2">
                                @if($employer->is_associated)
                                    <span class="badge bg-success">Si</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">CCC</label>
                            <p class="mb-2">{{ $employer->ccc ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">CNAE</label>
                            <p class="mb-2">{{ $employer->cnae ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-0">Tipo</label>
                            <p class="mb-2">
                                <span class="badge bg-info">{{ $employer->legal_form->type() }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos Persona Fisica / Empresa --}}
            @if($employer->freelancer)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-person me-2"></i>
                            Datos Persona Fisica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Completo</label>
                                <p class="mb-2 fw-semibold">{{ $employer->freelancer->first_name }} {{ $employer->freelancer->last_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">NISS</label>
                                <p class="mb-2"><code>{{ $employer->freelancer->niss ?? '-' }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Fecha Nacimiento</label>
                                <p class="mb-2">{{ $employer->freelancer->birthdate?->format('d/m/Y') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($employer->company)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-briefcase me-2"></i>
                            Datos Representante Legal
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Nombre Representante</label>
                                <p class="mb-2 fw-semibold">{{ $employer->company->representative_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">DNI/NIE Representante</label>
                                <p class="mb-2"><code>{{ $employer->company->representantive_identity_number ?? '-' }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Cargo</label>
                                <p class="mb-2">{{ $employer->company->representative_title ?? '-' }}</p>
                            </div>
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
                    <span class="badge bg-primary">{{ $employer->inmigrationFiles->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($employer->inmigrationFiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Titulo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employer->inmigrationFiles as $file)
                                        <tr>
                                            <td>
                                                <a href="{{ route('inmigration-files.show', $file->id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $file->file_code }}
                                                </a>
                                            </td>
                                            <td>{{ Str::limit($file->file_title, 40) }}</td>
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
                        @if($employer->email)
                            <p class="mb-0"><a href="mailto:{{ $employer->email }}">{{ $employer->email }}</a></p>
                        @else
                            <p class="mb-0 text-muted">-</p>
                        @endif
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Telefono</label>
                        @if($employer->phone)
                            <p class="mb-0"><a href="tel:{{ $employer->phone }}">{{ $employer->phone }}</a></p>
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
                    @if($employer->address)
                        <p class="mb-1">{{ $employer->address->street_name }} {{ $employer->address->number }}</p>
                        @if($employer->address->floor_door)
                            <p class="mb-1">{{ $employer->address->floor_door }}</p>
                        @endif
                        <p class="mb-1">{{ $employer->address->postal_code }} {{ $employer->address->municipality?->municipality_name }}</p>
                        <p class="mb-0">{{ $employer->address->province?->province_name }}, {{ $employer->address->country?->country_name }}</p>
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
                        <p class="mb-0 small">{{ $employer->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Actualizado</label>
                        <p class="mb-0 small">{{ $employer->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
