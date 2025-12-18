@extends('layouts.app')

@section('title', 'Ver Empleador')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2">
            <i class="bi bi-building"></i> Detalles del Empleador
        </h1>
    </div>
    <div class="col-auto">
        <div class="btn-group">
            <a href="{{ route('employers.edit', $employer->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('employers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información General</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nombre Fiscal:</strong>
                        <p class="mb-0">{{ $employer->fiscal_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Nombre Comercial:</strong>
                        <p class="mb-0">{{ $employer->comercial_name ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>NIF:</strong>
                        <p class="mb-0">{{ $employer->nif }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>CCC:</strong>
                        <p class="mb-0">{{ $employer->ccc }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>CNAE:</strong>
                        <p class="mb-0">{{ $employer->cnae }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $employer->email ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Teléfono:</strong>
                        <p class="mb-0">{{ $employer->phone ?? '-' }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Forma Legal:</strong>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ $employer->legal_form->value }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Asociado:</strong>
                        <p class="mb-0">
                            @if($employer->is_associated)
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($employer->freelancer)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Datos del Autónomo</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre:</strong>
                            <p class="mb-0">{{ $employer->freelancer->first_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Apellidos:</strong>
                            <p class="mb-0">{{ $employer->freelancer->last_name }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>NISS:</strong>
                            <p class="mb-0">{{ $employer->freelancer->niss }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de Nacimiento:</strong>
                            <p class="mb-0">{{ $employer->freelancer->birthdate->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($employer->company)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Datos del Representante</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre del Representante:</strong>
                            <p class="mb-0">{{ $employer->company->representative_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Cargo:</strong>
                            <p class="mb-0">{{ $employer->company->representative_title }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>DNI del Representante:</strong>
                            <p class="mb-0">{{ $employer->company->representantive_identity_number }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Información del Sistema</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $employer->id }}</p>
                <p><strong>Creado:</strong> {{ $employer->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Actualizado:</strong> {{ $employer->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Zona de Peligro</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Una vez eliminado, no podrá recuperar este registro.</p>
                <form action="{{ route('employers.destroy', $employer->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este empleador? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash"></i> Eliminar Empleador
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
