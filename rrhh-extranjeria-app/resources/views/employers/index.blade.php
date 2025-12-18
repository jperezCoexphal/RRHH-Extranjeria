@extends('layouts.app')

@section('title', 'Empleadores')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2">
            <i class="bi bi-building"></i> Empleadores
        </h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('employers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Empleador
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}" placeholder="Buscar por nombre...">
            </div>
            <div class="col-md-3">
                <label for="nif" class="form-label">NIF</label>
                <input type="text" class="form-control" id="nif" name="nif" value="{{ request('nif') }}" placeholder="Buscar por NIF...">
            </div>
            <div class="col-md-2">
                <label for="legal_form" class="form-label">Forma Legal</label>
                <select class="form-select" id="legal_form" name="legal_form">
                    <option value="">Todas</option>
                    @foreach(\App\Enums\LegalForm::cases() as $legalForm)
                        <option value="{{ $legalForm->value }}" {{ request('legal_form') === $legalForm->value ? 'selected' : '' }}>
                            {{ $legalForm->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="is_associated" class="form-label">Asociado</label>
                <select class="form-select" id="is_associated" name="is_associated">
                    <option value="">Todos</option>
                    <option value="1" {{ request('is_associated') === '1' ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ request('is_associated') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($employers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Fiscal</th>
                            <th>Nombre Comercial</th>
                            <th>NIF</th>
                            <th>Forma Legal</th>
                            <th>Asociado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employers as $employer)
                            <tr>
                                <td>{{ $employer->id }}</td>
                                <td>{{ $employer->fiscal_name }}</td>
                                <td>{{ $employer->comercial_name ?? '-' }}</td>
                                <td>{{ $employer->nif }}</td>
                                <td><span class="badge bg-info">{{ $employer->legal_form->name }}</span></td>
                                <td>
                                    @if($employer->is_associated)
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('employers.show', $employer->id) }}" class="btn btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('employers.edit', $employer->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('employers.destroy', $employer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este empleador?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $employers->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No se encontraron empleadores</p>
            </div>
        @endif
    </div>
</div>
@endsection
