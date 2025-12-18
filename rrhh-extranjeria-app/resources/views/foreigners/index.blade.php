@extends('layouts.app')

@section('title', 'Extranjeros')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2">
            <i class="bi bi-people"></i> Extranjeros
        </h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('foreigners.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Extranjero
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('foreigners.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}" placeholder="Buscar por nombre...">
            </div>
            <div class="col-md-2">
                <label for="nie" class="form-label">NIE</label>
                <input type="text" class="form-control" id="nie" name="nie" value="{{ request('nie') }}" placeholder="NIE...">
            </div>
            <div class="col-md-2">
                <label for="passport" class="form-label">Pasaporte</label>
                <input type="text" class="form-control" id="passport" name="passport" value="{{ request('passport') }}" placeholder="Pasaporte...">
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Género</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Todos</option>
                    @foreach(\App\Enums\Gender::cases() as $gender)
                        <option value="{{ $gender->value }}" {{ request('gender') === $gender->value ? 'selected' : '' }}>
                            {{ $gender->value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="nationality" class="form-label">Nacionalidad</label>
                <input type="text" class="form-control" id="nationality" name="nationality" value="{{ request('nationality') }}" placeholder="Nacionalidad...">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($foreigners->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>NIE</th>
                            <th>Pasaporte</th>
                            <th>Nacionalidad</th>
                            <th>Género</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($foreigners as $foreigner)
                            <tr>
                                <td>{{ $foreigner->id }}</td>
                                <td>{{ $foreigner->first_name }} {{ $foreigner->last_name }}</td>
                                <td>{{ $foreigner->nie }}</td>
                                <td>{{ $foreigner->passport }}</td>
                                <td>{{ $foreigner->nationality }}</td>
                                <td><span class="badge bg-info">{{ $foreigner->gender->value }}</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('foreigners.show', $foreigner->id) }}" class="btn btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('foreigners.edit', $foreigner->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('foreigners.destroy', $foreigner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este extranjero?')">
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
                {{ $foreigners->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No se encontraron extranjeros</p>
            </div>
        @endif
    </div>
</div>
@endsection
