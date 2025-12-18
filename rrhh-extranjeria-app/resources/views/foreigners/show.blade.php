@extends('layouts.app')

@section('title', 'Ver Extranjero')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2"><i class="bi bi-people"></i> Detalles del Extranjero</h1>
    </div>
    <div class="col-auto">
        <div class="btn-group">
            <a href="{{ route('foreigners.edit', $foreigner->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('foreigners.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Nombre:</strong><p class="mb-0">{{ $foreigner->first_name }}</p></div>
                    <div class="col-md-6"><strong>Apellidos:</strong><p class="mb-0">{{ $foreigner->last_name }}</p></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Pasaporte:</strong><p class="mb-0">{{ $foreigner->passport }}</p></div>
                    <div class="col-md-4"><strong>NIE:</strong><p class="mb-0">{{ $foreigner->nie }}</p></div>
                    <div class="col-md-4"><strong>NISS:</strong><p class="mb-0">{{ $foreigner->niss ?? '-' }}</p></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Género:</strong><p class="mb-0"><span class="badge bg-info">{{ $foreigner->gender->value }}</span></p></div>
                    <div class="col-md-4"><strong>Fecha de Nacimiento:</strong><p class="mb-0">{{ $foreigner->birthdate->format('d/m/Y') }}</p></div>
                    <div class="col-md-4"><strong>Nacionalidad:</strong><p class="mb-0">{{ $foreigner->nationality }}</p></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Estado Civil:</strong><p class="mb-0"><span class="badge bg-secondary">{{ $foreigner->marital_status->value }}</span></p></div>
                </div>
            </div>
        </div>

        @if($foreigner->extraData)
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Datos Adicionales</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Padre:</strong><p class="mb-0">{{ $foreigner->extraData->father_name ?? '-' }}</p></div>
                    <div class="col-md-6"><strong>Madre:</strong><p class="mb-0">{{ $foreigner->extraData->mother_name ?? '-' }}</p></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Teléfono:</strong><p class="mb-0">{{ $foreigner->extraData->phone ?? '-' }}</p></div>
                    <div class="col-md-6"><strong>Email:</strong><p class="mb-0">{{ $foreigner->extraData->email ?? '-' }}</p></div>
                </div>
                @if($foreigner->extraData->legal_guardian_name)
                <div class="row">
                    <div class="col-md-6"><strong>Tutor Legal:</strong><p class="mb-0">{{ $foreigner->extraData->legal_guardian_name }}</p></div>
                    <div class="col-md-3"><strong>DNI Tutor:</strong><p class="mb-0">{{ $foreigner->extraData->legal_guardian_identity_number ?? '-' }}</p></div>
                    <div class="col-md-3"><strong>Título:</strong><p class="mb-0">{{ $foreigner->extraData->guardianship_title ?? '-' }}</p></div>
                </div>
                @endif
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
                <p><strong>ID:</strong> {{ $foreigner->id }}</p>
                <p><strong>Creado:</strong> {{ $foreigner->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Actualizado:</strong> {{ $foreigner->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Zona de Peligro</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Una vez eliminado, no podrá recuperar este registro.</p>
                <form action="{{ route('foreigners.destroy', $foreigner->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este extranjero? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash"></i> Eliminar Extranjero
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
