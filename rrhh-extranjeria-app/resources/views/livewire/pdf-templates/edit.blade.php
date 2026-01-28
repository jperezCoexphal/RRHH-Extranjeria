<?php

use App\Models\PdfTemplate;
use App\Enums\PdfTemplateType;
use App\Enums\ApplicationType;
use App\DTOs\PdfTemplateDTO;
use App\Services\PdfTemplateService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

new
#[Layout('layouts.app')]
#[Title('Editar Plantilla PDF - RRHH Extranjeria')]
class extends Component {
    use WithFileUploads;

    public PdfTemplate $pdfTemplate;

    // Basic info
    public string $name = '';
    public string $description = '';
    public string $document_type = '';
    public string $application_type = '';
    public bool $is_default = false;
    public bool $is_active = true;
    public $newFile = null;

    // Field mapping
    public array $fieldMapping = [];
    public array $extractedFields = [];

    // UI State
    public string $activeTab = 'info';

    public function mount(PdfTemplate $pdfTemplate): void
    {
        $this->pdfTemplate = $pdfTemplate;
        $this->name = $pdfTemplate->name;
        $this->description = $pdfTemplate->description ?? '';
        $this->document_type = $pdfTemplate->document_type->value;
        $this->application_type = $pdfTemplate->application_type?->value ?? '';
        $this->is_default = $pdfTemplate->is_default;
        $this->is_active = $pdfTemplate->is_active;
        $this->extractedFields = $pdfTemplate->extracted_fields ?? [];
        $this->fieldMapping = $pdfTemplate->field_mapping ?? [];

        // Initialize empty mappings for fields without mapping
        foreach ($this->extractedFields as $field) {
            if (!isset($this->fieldMapping[$field['name']])) {
                $this->fieldMapping[$field['name']] = [
                    'source' => '',
                    'field' => '',
                    'format' => null,
                    'type' => $field['type'],
                    'checked_when' => '',
                ];
            } elseif (!isset($this->fieldMapping[$field['name']]['checked_when'])) {
                $this->fieldMapping[$field['name']]['checked_when'] = '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:' . implode(',', PdfTemplateType::values()),
            'application_type' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'newFile' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveInfo(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:' . implode(',', PdfTemplateType::values()),
            'application_type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $service = app(PdfTemplateService::class);

        try {
            $dto = new PdfTemplateDTO(
                name: $this->name,
                documentType: PdfTemplateType::from($this->document_type),
                description: $this->description ?: null,
                applicationType: $this->application_type ? ApplicationType::from($this->application_type) : null,
                file: $this->newFile,
                isDefault: $this->is_default,
                isActive: $this->is_active,
            );

            $service->update($this->pdfTemplate->id, $dto);

            if ($this->is_default) {
                $service->setAsDefault($this->pdfTemplate->id);
            }

            // Reload template
            $this->pdfTemplate->refresh();
            $this->extractedFields = $this->pdfTemplate->extracted_fields ?? [];

            // Reinitialize mappings if file changed
            if ($this->newFile) {
                $this->fieldMapping = [];
                foreach ($this->extractedFields as $field) {
                    $this->fieldMapping[$field['name']] = [
                        'source' => '',
                        'field' => '',
                        'format' => null,
                        'type' => $field['type'],
                    ];
                }
                $this->newFile = null;
            }

            session()->flash('success', 'Plantilla actualizada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function saveMapping(): void
    {
        $service = app(PdfTemplateService::class);

        try {
            $filteredMapping = array_filter($this->fieldMapping, fn($m) => !empty($m['field']));
            $service->updateFieldMapping($this->pdfTemplate->id, $filteredMapping);

            $this->pdfTemplate->refresh();
            session()->flash('success', 'Mapeo de campos actualizado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el mapeo: ' . $e->getMessage());
        }
    }

    public function reExtractFields(): void
    {
        $service = app(PdfTemplateService::class);
        $fields = $service->reExtractFields($this->pdfTemplate->id);

        if ($fields) {
            $this->extractedFields = $fields;
            $this->pdfTemplate->refresh();

            // Reinitialize mappings
            $this->fieldMapping = $this->pdfTemplate->field_mapping ?? [];
            foreach ($this->extractedFields as $field) {
                if (!isset($this->fieldMapping[$field['name']])) {
                    $this->fieldMapping[$field['name']] = [
                        'source' => '',
                        'field' => '',
                        'format' => null,
                        'type' => $field['type'],
                    ];
                }
            }

            session()->flash('success', 'Campos re-extraidos correctamente.');
        } else {
            session()->flash('error', 'No se pudieron extraer los campos del PDF.');
        }
    }

    public function clearMapping(string $fieldName): void
    {
        $this->fieldMapping[$fieldName] = [
            'source' => '',
            'field' => '',
            'format' => null,
            'type' => $this->fieldMapping[$fieldName]['type'] ?? 'text',
        ];
    }

    public function with(): array
    {
        $service = app(PdfTemplateService::class);

        return [
            'documentTypes' => PdfTemplateType::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'dataSources' => $service->getAvailableDataSources(),
            'mappedCount' => count(array_filter($this->fieldMapping, fn($m) => !empty($m['field']))),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Editar Plantilla PDF')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pdf-templates.index') }}">Plantillas PDF</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">
                Editar: {{ $pdfTemplate->name }}
                @if($pdfTemplate->is_default)
                    <i class="bi bi-star-fill text-warning" title="Predeterminada"></i>
                @endif
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pdf-templates.show', $pdfTemplate->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>
                Ver Detalles
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button wire:click="setTab('info')"
                    class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}">
                <i class="bi bi-info-circle me-1"></i>
                Informacion General
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('mapping')"
                    class="nav-link {{ $activeTab === 'mapping' ? 'active' : '' }}">
                <i class="bi bi-diagram-3 me-1"></i>
                Mapeo de Campos
                <span class="badge bg-{{ $mappedCount === count($extractedFields) ? 'success' : 'secondary' }} ms-1">
                    {{ $mappedCount }}/{{ count($extractedFields) }}
                </span>
            </button>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            {{-- Tab: Info --}}
            @if($activeTab === 'info')
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-pencil me-2"></i>
                            Datos de la Plantilla
                        </h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit="saveInfo">
                            {{-- Nombre y Descripcion --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text"
                                           wire:model="name"
                                           class="form-control @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripcion</label>
                                    <textarea wire:model="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="2"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tipo de documento y solicitud --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                    <select wire:model="document_type" class="form-select @error('document_type') is-invalid @enderror">
                                        @foreach($documentTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('document_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Solicitud</label>
                                    <select wire:model="application_type" class="form-select">
                                        <option value="">General (todos los tipos)</option>
                                        @foreach($applicationTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Cambiar archivo PDF --}}
                            <div class="mb-4">
                                <label class="form-label">Cambiar Archivo PDF</label>
                                <input type="file"
                                       wire:model="newFile"
                                       class="form-control @error('newFile') is-invalid @enderror"
                                       accept=".pdf">
                                <small class="text-muted">
                                    Archivo actual: <strong>{{ $pdfTemplate->original_filename }}</strong>
                                    @if($pdfTemplate->file_size)
                                        ({{ $pdfTemplate->file_size }})
                                    @endif
                                </small>
                                @error('newFile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div wire:loading wire:target="newFile" class="mt-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="text-muted ms-2">Subiendo...</span>
                                </div>

                                @if($newFile)
                                    <div class="alert alert-warning mt-2 mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Al cambiar el PDF, el mapeo de campos se reiniciara.
                                    </div>
                                @endif
                            </div>

                            {{-- Opciones --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox"
                                               wire:model="is_default"
                                               class="form-check-input"
                                               id="is_default">
                                        <label class="form-check-label" for="is_default">
                                            <strong>Plantilla Predeterminada</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox"
                                               wire:model="is_active"
                                               class="form-check-input"
                                               id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            <strong>Plantilla Activa</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveInfo">
                                        <i class="bi bi-check-lg me-1"></i>
                                        Guardar Cambios
                                    </span>
                                    <span wire:loading wire:target="saveInfo">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        Guardando...
                                    </span>
                                </button>
                                <a href="{{ route('pdf-templates.index') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Tab: Mapping --}}
            @if($activeTab === 'mapping')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Mapeo de Campos
                        </h6>
                        <div class="d-flex gap-2">
                            <button wire:click="reExtractFields" class="btn btn-sm btn-outline-secondary" wire:loading.attr="disabled">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Re-extraer campos
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($extractedFields) === 0)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No hay campos detectados en este PDF.
                            </div>
                        @else
                            <p class="text-muted mb-4">
                                Asocia cada campo del PDF con un dato del sistema.
                                Los campos no mapeados quedaran vacios al generar documentos.
                            </p>

                            <div class="table-responsive mb-4">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">Campo PDF</th>
                                            <th style="width: 15%;">Fuente</th>
                                            <th style="width: 25%;">Campo del Sistema</th>
                                            <th style="width: 20%;">Condicion</th>
                                            <th style="width: 10%;">Estado</th>
                                            <th style="width: 10%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($extractedFields as $index => $field)
                                            @php $isCheckbox = ($field['type'] ?? 'text') === 'checkbox'; @endphp
                                            <tr wire:key="edit-mapping-{{ $index }}">
                                                <td>
                                                    <code class="small">{{ Str::limit($field['name'], 20) }}</code>
                                                    @if($isCheckbox)
                                                        <span class="badge bg-warning text-dark ms-1" title="Checkbox">
                                                            <i class="bi bi-check-square"></i>
                                                        </span>
                                                    @endif
                                                    @if($field['is_required'] ?? false)
                                                        <span class="badge bg-danger ms-1">Req</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <select wire:model.live="fieldMapping.{{ $field['name'] }}.source"
                                                            class="form-select form-select-sm">
                                                        <option value="">Sin mapear</option>
                                                        @foreach($dataSources as $sourceKey => $source)
                                                            <option value="{{ $sourceKey }}">{{ $source['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    @if($fieldMapping[$field['name']]['source'] ?? false)
                                                        <select wire:model.live="fieldMapping.{{ $field['name'] }}.field"
                                                                class="form-select form-select-sm">
                                                            <option value="">Seleccionar...</option>
                                                            @foreach($dataSources[$fieldMapping[$field['name']]['source']]['fields'] ?? [] as $fieldKey => $fieldLabel)
                                                                <option value="{{ $fieldKey }}">{{ is_array($fieldLabel) ? $fieldLabel['label'] : $fieldLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isCheckbox && ($fieldMapping[$field['name']]['field'] ?? false))
                                                        @php
                                                            $mappedSource = $fieldMapping[$field['name']]['source'] ?? '';
                                                            $mappedField = $fieldMapping[$field['name']]['field'] ?? '';
                                                            $fieldDef = $dataSources[$mappedSource]['fields'][$mappedField] ?? null;
                                                            $enumOptions = is_array($fieldDef) ? ($fieldDef['options'] ?? []) : [];
                                                        @endphp

                                                        @if(count($enumOptions) > 0)
                                                            <select wire:model.blur="fieldMapping.{{ $field['name'] }}.checked_when"
                                                                    class="form-select form-select-sm"
                                                                    title="Marcar cuando el valor sea igual a...">
                                                                <option value="">Seleccionar valor...</option>
                                                                @foreach($enumOptions as $opt)
                                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input type="text"
                                                                   wire:model.blur="fieldMapping.{{ $field['name'] }}.checked_when"
                                                                   class="form-control form-control-sm"
                                                                   placeholder="Valor para marcar"
                                                                   title="Marcar cuando el valor sea igual a...">
                                                        @endif
                                                    @elseif($isCheckbox)
                                                        <span class="text-muted small">-</span>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($fieldMapping[$field['name']]['field']))
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check"></i>
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($fieldMapping[$field['name']]['field']))
                                                        <button wire:click="clearMapping('{{ $field['name'] }}')"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                title="Limpiar mapeo">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2">
                                <button wire:click="saveMapping" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveMapping">
                                        <i class="bi bi-check-lg me-1"></i>
                                        Guardar Mapeo
                                    </span>
                                    <span wire:loading wire:target="saveMapping">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Template Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Informacion del Archivo
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Archivo:</td>
                            <td class="text-end">{{ $pdfTemplate->original_filename }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tamaño:</td>
                            <td class="text-end">{{ $pdfTemplate->file_size ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Campos:</td>
                            <td class="text-end">{{ $pdfTemplate->total_fields }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Mapeados:</td>
                            <td class="text-end">{{ $mappedCount }} ({{ $pdfTemplate->mapping_progress }}%)</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Creado:</td>
                            <td class="text-end">{{ $pdfTemplate->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Progress Card --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Progreso del Mapeo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-{{ $pdfTemplate->mapping_progress == 100 ? 'success' : ($pdfTemplate->mapping_progress > 50 ? 'warning' : 'primary') }}"
                             style="width: {{ $pdfTemplate->mapping_progress }}%">
                            {{ $pdfTemplate->mapping_progress }}%
                        </div>
                    </div>

                    @if($pdfTemplate->mapping_progress < 100)
                        <p class="small text-muted mb-0">
                            Faltan {{ count($extractedFields) - $mappedCount }} campos por mapear.
                            Los campos no mapeados quedaran vacios.
                        </p>
                    @else
                        <p class="small text-success mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Todos los campos estan mapeados.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
