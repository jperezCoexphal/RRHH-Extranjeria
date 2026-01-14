@extends('layouts.app')

@section('page-title', 'Checklist - ' . $file->file_code)

@section('content')
<div>
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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.index') }}">Expedientes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inmigration-files.show', $file->id) }}">{{ $file->file_code }}</a></li>
                    <li class="breadcrumb-item active">Checklist</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Checklist de Requisitos</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inmigration-files.show', $file->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al Expediente
            </a>
            <form action="{{ route('checklist.regenerate', $file->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary" onclick="return confirm('¿Regenerar requisitos desde las plantillas?')">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Regenerar
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Contenido Principal --}}
        <div class="col-lg-8">
            {{-- Resumen Visual --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-end">
                            <div class="display-4 fw-bold text-{{ $summary['completion_percentage'] == 100 ? 'success' : ($summary['completion_percentage'] >= 50 ? 'primary' : 'warning') }}">
                                {{ $summary['completion_percentage'] }}%
                            </div>
                            <p class="text-muted mb-0">Completado</p>
                        </div>
                        <div class="col-md-8">
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ $summary['completion_percentage'] }}%">
                                    {{ $summary['completed'] }}/{{ $summary['total'] }}
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col">
                                    <span class="badge bg-success fs-6">{{ $summary['completed'] }}</span>
                                    <small class="d-block text-muted">Completados</small>
                                </div>
                                <div class="col">
                                    <span class="badge bg-warning fs-6">{{ $summary['pending'] }}</span>
                                    <small class="d-block text-muted">Pendientes</small>
                                </div>
                                <div class="col">
                                    <span class="badge bg-danger fs-6">{{ $summary['mandatory_pending'] }}</span>
                                    <small class="d-block text-muted">Oblig. Pend.</small>
                                </div>
                                <div class="col">
                                    <span class="badge bg-dark fs-6">{{ $summary['overdue'] }}</span>
                                    <small class="d-block text-muted">Vencidos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Requisitos por Entidad --}}
            @foreach($targetEntities as $entity)
                @php
                    $entityRequirements = $requirements->where('target_entity', $entity);
                    $entityCompleted = $entityRequirements->where('is_completed', true)->count();
                    $entityTotal = $entityRequirements->count();
                    $entityPending = $entityTotal - $entityCompleted;
                    $entityOverdue = $entityRequirements->filter(fn($r) => !$r->is_completed && $r->due_date?->isPast())->count();
                @endphp

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">
                            @switch($entity->value)
                                @case('GENERAL')
                                    <i class="bi bi-folder2-open me-2"></i>
                                    @break
                                @case('EMPLOYER')
                                    <i class="bi bi-building me-2"></i>
                                    @break
                                @case('WORKER')
                                    <i class="bi bi-person me-2"></i>
                                    @break
                                @case('REPRESENTATIVE')
                                    <i class="bi bi-person-badge me-2"></i>
                                    @break
                            @endswitch
                            {{ $entity->label() }}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $entityPending == 0 && $entityTotal > 0 ? 'success' : ($entityOverdue > 0 ? 'danger' : 'warning') }}">
                                {{ $entityCompleted }}/{{ $entityTotal }}
                            </span>
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addRequirementModal"
                                    onclick="setTargetEntity('{{ $entity->value }}')">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($entityRequirements->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($entityRequirements as $req)
                                    <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                                        <div class="d-flex align-items-start flex-grow-1">
                                            <form action="{{ route('checklist.complete', $req->id) }}" method="POST" class="me-3">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                                    @if($req->is_completed)
                                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                    @else
                                                        <i class="bi bi-circle text-muted fs-5"></i>
                                                    @endif
                                                </button>
                                            </form>
                                            <div class="{{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                                <strong>{{ $req->name }}</strong>
                                                @if($req->is_mandatory)
                                                    <span class="badge bg-danger ms-1">Obligatorio</span>
                                                @else
                                                    <span class="badge bg-secondary ms-1">Opcional</span>
                                                @endif
                                                @if($req->requirement_template_id)
                                                    <span class="badge bg-info ms-1">Plantilla</span>
                                                @endif
                                                @if($req->description)
                                                    <p class="text-muted small mb-0 mt-1">{{ $req->description }}</p>
                                                @endif
                                                @if($req->due_date)
                                                    <small class="text-{{ $req->due_date->isPast() && !$req->is_completed ? 'danger fw-bold' : 'muted' }}">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        Vence: {{ $req->due_date->format('d/m/Y') }}
                                                        @if($req->due_date->isPast() && !$req->is_completed)
                                                            (Vencido)
                                                        @elseif(!$req->is_completed)
                                                            ({{ $req->due_date->diffForHumans() }})
                                                        @endif
                                                    </small>
                                                @endif
                                                @if($req->is_completed && $req->completed_at)
                                                    <small class="text-success d-block">
                                                        <i class="bi bi-check me-1"></i>
                                                        Completado: {{ $req->completed_at->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!$req->is_completed && !$req->requirement_template_id)
                                            <form action="{{ route('checklist.destroy', $req->id) }}" method="POST" class="ms-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este requisito?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-check2-all text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">Sin requisitos para {{ $entity->label() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Requisitos sin entidad asignada --}}
            @php
                $noEntityRequirements = $requirements->whereNull('target_entity');
            @endphp
            @if($noEntityRequirements->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-question-circle me-2"></i>
                            Sin Entidad Asignada
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($noEntityRequirements as $req)
                                <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-start flex-grow-1">
                                        <form action="{{ route('checklist.complete', $req->id) }}" method="POST" class="me-3">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                                @if($req->is_completed)
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                @else
                                                    <i class="bi bi-circle text-muted fs-5"></i>
                                                @endif
                                            </button>
                                        </form>
                                        <div class="{{ $req->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                            <strong>{{ $req->name }}</strong>
                                            @if($req->is_mandatory)
                                                <span class="badge bg-danger ms-1">Obligatorio</span>
                                            @else
                                                <span class="badge bg-secondary ms-1">Opcional</span>
                                            @endif
                                            @if($req->description)
                                                <p class="text-muted small mb-0 mt-1">{{ $req->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Info del Expediente --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-folder2-open me-2"></i>
                        Expediente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Codigo</label>
                        <p class="mb-0 fw-bold">{{ $file->file_code }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Titulo</label>
                        <p class="mb-0">{{ $file->file_title }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Estado</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $statusColors[$file->status->value] ?? 'secondary' }}">
                                {{ $file->status->label() }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-0">Tipo Solicitud</label>
                        <p class="mb-0">{{ $file->application_type->value }}</p>
                    </div>
                </div>
            </div>

            {{-- Estado de Generacion --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Generacion de Documentos
                    </h6>
                </div>
                <div class="card-body">
                    @if($summary['mandatory_pending'] == 0)
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Listo para generar</strong>
                            <p class="mb-0 small">Todos los requisitos obligatorios estan completados.</p>
                        </div>
                        <a href="{{ route('documents.show', $file->id) }}" class="btn btn-success w-100">
                            <i class="bi bi-file-earmark-plus me-1"></i>
                            Generar Documentos
                        </a>
                    @else
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Requisitos pendientes</strong>
                            <p class="mb-0 small">Faltan {{ $summary['mandatory_pending'] }} requisito(s) obligatorio(s).</p>
                        </div>
                        <a href="{{ route('documents.show', $file->id) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-file-earmark me-1"></i>
                            Ver Documentos
                        </a>
                    @endif
                </div>
            </div>

            {{-- Leyenda --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Leyenda
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-danger me-2">Obligatorio</span>
                        <span>Debe completarse para generar documentos</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">Opcional</span>
                        <span>No bloquea la generacion</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">Plantilla</span>
                        <span>Generado automaticamente</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        <span>Requisito vencido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Agregar Requisito --}}
<div class="modal fade" id="addRequirementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('checklist.store', $file->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        Agregar Requisito
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="target_entity" id="targetEntityInput">

                    <div class="mb-3">
                        <label class="form-label">Nombre del Requisito <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Contrato firmado">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Descripcion opcional..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Limite</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_mandatory" value="1" class="form-check-input" id="isMandatoryCheck">
                                <label class="form-check-label" for="isMandatoryCheck">
                                    <strong>Obligatorio</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus me-1"></i>
                        Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function setTargetEntity(entity) {
        document.getElementById('targetEntityInput').value = entity;
    }
</script>
@endpush
@endsection
