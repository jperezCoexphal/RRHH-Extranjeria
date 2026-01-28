<?php

use App\Models\PdfTemplate;
use App\Services\PdfTemplateService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Detalle Plantilla PDF - RRHH Extranjeria')]
class extends Component {
    public PdfTemplate $pdfTemplate;

    public function mount(PdfTemplate $pdfTemplate): void
    {
        $this->pdfTemplate = $pdfTemplate->load('creator');
    }

    public function setAsDefault(): void
    {
        $service = app(PdfTemplateService::class);
        if ($service->setAsDefault($this->pdfTemplate->id)) {
            $this->pdfTemplate->refresh();
            session()->flash('success', 'Plantilla establecida como predeterminada.');
        }
    }

    public function toggleActive(): void
    {
        $this->pdfTemplate->update(['is_active' => !$this->pdfTemplate->is_active]);
        $this->pdfTemplate->refresh();
        session()->flash('success', $this->pdfTemplate->is_active ? 'Plantilla activada.' : 'Plantilla desactivada.');
    }

    public function delete(): void
    {
        $this->pdfTemplate->delete();
        session()->flash('success', 'Plantilla eliminada correctamente.');
        $this->redirect(route('pdf-templates.index'), navigate: true);
    }

    public function duplicate(): void
    {
        $service = app(PdfTemplateService::class);
        $newTemplate = $service->duplicate($this->pdfTemplate->id);

        if ($newTemplate) {
            session()->flash('success', 'Plantilla duplicada correctamente.');
            $this->redirect(route('pdf-templates.edit', $newTemplate->id), navigate: true);
        } else {
            session()->flash('error', 'No se pudo duplicar la plantilla.');
        }
    }

    public function downloadPdf(): mixed
    {
        if (!$this->pdfTemplate->file_exists) {
            session()->flash('error', 'El archivo PDF no existe.');
            return null;
        }

        return response()->download(
            $this->pdfTemplate->file_path,
            $this->pdfTemplate->original_filename
        );
    }

    public function with(): array
    {
        $service = app(PdfTemplateService::class);

        return [
            'dataSources' => $service->getAvailableDataSources(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Detalle Plantilla PDF')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pdf-templates.index') }}">Plantillas PDF</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">
                <i class="bi {{ $pdfTemplate->document_type->icon() }} text-{{ $pdfTemplate->document_type->badgeColor() }} me-2"></i>
                {{ $pdfTemplate->name }}
                @if($pdfTemplate->is_default)
                    <i class="bi bi-star-fill text-warning" title="Predeterminada"></i>
                @endif
                @if(!$pdfTemplate->is_active)
                    <span class="badge bg-secondary ms-2">Inactiva</span>
                @endif
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pdf-templates.edit', $pdfTemplate->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>
                Editar
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(!$pdfTemplate->is_default)
                        <li>
                            <button wire:click="setAsDefault" class="dropdown-item">
                                <i class="bi bi-star me-2"></i>
                                Establecer como predeterminada
                            </button>
                        </li>
                    @endif
                    <li>
                        <button wire:click="toggleActive" class="dropdown-item">
                            <i class="bi bi-{{ $pdfTemplate->is_active ? 'pause' : 'play' }} me-2"></i>
                            {{ $pdfTemplate->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </li>
                    <li>
                        <button wire:click="duplicate" class="dropdown-item">
                            <i class="bi bi-copy me-2"></i>
                            Duplicar
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button wire:click="delete"
                                wire:confirm="¿Estas seguro de eliminar esta plantilla?"
                                class="dropdown-item text-danger">
                            <i class="bi bi-trash me-2"></i>
                            Eliminar
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informacion General
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Nombre:</td>
                                    <td><strong>{{ $pdfTemplate->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tipo de Documento:</td>
                                    <td>
                                        <span class="badge bg-{{ $pdfTemplate->document_type->badgeColor() }}">
                                            {{ $pdfTemplate->document_type->label() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tipo de Solicitud:</td>
                                    <td>
                                        @if($pdfTemplate->application_type)
                                            <span class="badge bg-secondary">{{ $pdfTemplate->application_type->name }}</span>
                                        @else
                                            <span class="text-muted">General (todos)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estado:</td>
                                    <td>
                                        @if($pdfTemplate->is_active)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-secondary">Inactiva</span>
                                        @endif
                                        @if($pdfTemplate->is_default)
                                            <span class="badge bg-warning text-dark ms-1">Predeterminada</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Creado:</td>
                                    <td>{{ $pdfTemplate->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Creado por:</td>
                                    <td>{{ $pdfTemplate->creator?->name ?? 'Sistema' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Actualizado:</td>
                                    <td>{{ $pdfTemplate->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($pdfTemplate->description)
                        <hr>
                        <h6 class="text-muted mb-2">Descripcion</h6>
                        <p class="mb-0">{{ $pdfTemplate->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Fields and Mapping Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-list-check me-2"></i>
                        Campos del Formulario
                    </h6>
                    <span class="badge bg-primary">{{ $pdfTemplate->total_fields }} campos</span>
                </div>
                <div class="card-body p-0">
                    @if($pdfTemplate->total_fields === 0)
                        <div class="text-center py-5">
                            <i class="bi bi-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No hay campos detectados en este PDF</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Campo PDF</th>
                                        <th>Tipo</th>
                                        <th>Mapeado a</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pdfTemplate->extracted_fields ?? [] as $field)
                                        @php
                                            $mapping = $pdfTemplate->field_mapping[$field['name']] ?? null;
                                            $isMapped = $mapping && !empty($mapping['field']);
                                        @endphp
                                        <tr>
                                            <td>
                                                <code class="small">{{ $field['name'] }}</code>
                                                @if($field['is_required'] ?? false)
                                                    <span class="badge bg-danger ms-1">Req</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ ucfirst($field['type']) }}</span>
                                            </td>
                                            <td>
                                                @if($isMapped)
                                                    @php
                                                        $fieldDef = $dataSources[$mapping['source']]['fields'][$mapping['field']] ?? $mapping['field'];
                                                        $fieldLabel = is_array($fieldDef) ? ($fieldDef['label'] ?? $mapping['field']) : $fieldDef;
                                                    @endphp
                                                    <span class="text-success">
                                                        <i class="bi bi-arrow-right me-1"></i>
                                                        {{ $dataSources[$mapping['source']]['label'] ?? $mapping['source'] }}:
                                                        <strong>{{ $fieldLabel }}</strong>
                                                    </span>
                                                @else
                                                    <span class="text-muted">Sin mapear</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($isMapped)
                                                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                                @else
                                                    <span class="badge bg-light text-dark">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if($pdfTemplate->total_fields > 0)
                    <div class="card-footer">
                        <a href="{{ route('pdf-templates.edit', $pdfTemplate->id) }}?tab=mapping" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>
                            Editar Mapeo
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- File Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Archivo PDF
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 4rem;"></i>
                    </div>

                    <table class="table table-sm mb-3">
                        <tr>
                            <td class="text-muted">Nombre:</td>
                            <td class="text-end small">{{ $pdfTemplate->original_filename }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tamaño:</td>
                            <td class="text-end">{{ $pdfTemplate->file_size ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Existe:</td>
                            <td class="text-end">
                                @if($pdfTemplate->file_exists)
                                    <span class="badge bg-success">Si</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($pdfTemplate->file_exists)
                        <a href="{{ route('pdf-templates.download', $pdfTemplate->id) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-download me-1"></i>
                            Descargar PDF
                        </a>
                    @endif
                </div>
            </div>

            {{-- Mapping Progress Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Progreso del Mapeo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Campos mapeados</span>
                        <strong>{{ $pdfTemplate->mapped_fields_count }}/{{ $pdfTemplate->total_fields }}</strong>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-{{ $pdfTemplate->mapping_progress == 100 ? 'success' : ($pdfTemplate->mapping_progress > 50 ? 'warning' : 'primary') }}"
                             style="width: {{ $pdfTemplate->mapping_progress }}%"></div>
                    </div>

                    @if($pdfTemplate->mapping_progress == 100)
                        <div class="alert alert-success mb-0 py-2">
                            <i class="bi bi-check-circle me-1"></i>
                            Mapeo completo
                        </div>
                    @elseif($pdfTemplate->mapping_progress > 0)
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Mapeo parcial ({{ $pdfTemplate->mapping_progress }}%)
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0 py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Sin mapeo configurado
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones Rapidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pdf-templates.edit', $pdfTemplate->id) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-1"></i>
                            Editar Plantilla
                        </a>
                        @if(!$pdfTemplate->is_default)
                            <button wire:click="setAsDefault" class="btn btn-outline-primary">
                                <i class="bi bi-star me-1"></i>
                                Establecer como Predeterminada
                            </button>
                        @endif
                        <button wire:click="duplicate" class="btn btn-outline-info">
                            <i class="bi bi-copy me-1"></i>
                            Duplicar Plantilla
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
