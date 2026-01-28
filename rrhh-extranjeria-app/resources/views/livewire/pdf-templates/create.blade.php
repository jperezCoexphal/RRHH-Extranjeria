<?php

use App\Enums\PdfTemplateType;
use App\Enums\ApplicationType;
use App\DTOs\PdfTemplateDTO;
use App\Services\PdfTemplateService;
use App\Services\PdfFieldExtractorService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app')]
#[Title('Nueva Plantilla PDF - RRHH Extranjeria')]
class extends Component {
    use WithFileUploads;

    // Step management
    public int $currentStep = 1;

    // Step 1: Basic info
    public string $name = '';
    public string $description = '';
    public string $document_type = '';
    public string $application_type = '';
    public bool $is_default = false;
    public $file = null;

    // Step 2: Extracted fields (populated after upload)
    public array $extractedFields = [];
    public int $totalFields = 0;
    public ?string $extractionError = null;

    // Step 3: Field mapping
    public array $fieldMapping = [];

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:' . implode(',', PdfTemplateType::values()),
            'application_type' => 'nullable|string',
            'is_default' => 'boolean',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'file.required' => 'Debes seleccionar un archivo PDF.',
            'file.mimes' => 'El archivo debe ser un PDF.',
            'file.max' => 'El archivo no puede superar los 10MB.',
        ];
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validateStep1();
            $this->extractFields();
            $this->currentStep = 2;
        } elseif ($this->currentStep === 2) {
            $this->currentStep = 3;
            $this->initializeFieldMapping();
        }
    }

    protected function validateStep1(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'document_type' => 'required|string|in:' . implode(',', PdfTemplateType::values()),
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);
    }

    protected function extractFields(): void
    {
        $this->extractionError = null;

        try {
            $extractor = app(PdfFieldExtractorService::class);
            $tempPath = $this->file->getRealPath();

            $fields = $extractor->extractFields($tempPath);

            $this->extractedFields = array_map(fn($f) => $f->toArray(), $fields);
            $this->totalFields = count($this->extractedFields);
        } catch (\Exception $e) {
            $this->extractionError = $e->getMessage();
            $this->extractedFields = [];
            $this->totalFields = 0;
        }
    }

    protected function initializeFieldMapping(): void
    {
        $this->fieldMapping = [];

        foreach ($this->extractedFields as $field) {
            $this->fieldMapping[$field['name']] = [
                'source' => '',
                'field' => '',
                'format' => null,
                'type' => $field['type'],
                'checked_when' => '',
            ];
        }
    }

    public function updateMapping(string $pdfField, string $source, string $dataField): void
    {
        $this->fieldMapping[$pdfField]['source'] = $source;
        $this->fieldMapping[$pdfField]['field'] = $dataField;
    }

    public function save(): void
    {
        $service = app(PdfTemplateService::class);

        try {
            $dto = new PdfTemplateDTO(
                name: $this->name,
                documentType: PdfTemplateType::from($this->document_type),
                description: $this->description ?: null,
                applicationType: $this->application_type ? ApplicationType::from($this->application_type) : null,
                file: $this->file,
                isDefault: $this->is_default,
                isActive: true,
                fieldMapping: array_filter($this->fieldMapping, fn($m) => !empty($m['field'])),
                createdBy: auth()->id(),
            );

            $template = $service->create($dto);

            session()->flash('success', 'Plantilla PDF creada correctamente.');
            $this->redirect(route('pdf-templates.show', $template->id), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la plantilla: ' . $e->getMessage());
        }
    }

    public function saveWithoutMapping(): void
    {
        $service = app(PdfTemplateService::class);

        try {
            $dto = new PdfTemplateDTO(
                name: $this->name,
                documentType: PdfTemplateType::from($this->document_type),
                description: $this->description ?: null,
                applicationType: $this->application_type ? ApplicationType::from($this->application_type) : null,
                file: $this->file,
                isDefault: $this->is_default,
                isActive: true,
                fieldMapping: null,
                createdBy: auth()->id(),
            );

            $template = $service->create($dto);

            session()->flash('success', 'Plantilla PDF creada. Puedes configurar el mapeo de campos mas tarde.');
            $this->redirect(route('pdf-templates.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la plantilla: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        $service = app(PdfTemplateService::class);

        return [
            'documentTypes' => PdfTemplateType::cases(),
            'applicationTypes' => ApplicationType::cases(),
            'dataSources' => $service->getAvailableDataSources(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Nueva Plantilla PDF')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pdf-templates.index') }}">Plantillas PDF</a></li>
                    <li class="breadcrumb-item active">Nueva</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Nueva Plantilla PDF</h4>
        </div>
    </div>

    {{-- Steps Indicator --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between">
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $currentStep >= 1 ? 'bg-primary text-white' : 'bg-light' }}" style="width: 40px; height: 40px;">
                        @if($currentStep > 1)
                            <i class="bi bi-check-lg"></i>
                        @else
                            1
                        @endif
                    </div>
                    <p class="small mb-0 mt-1 {{ $currentStep >= 1 ? 'text-primary fw-semibold' : 'text-muted' }}">Subir PDF</p>
                </div>
                <div class="flex-fill d-flex align-items-center" style="margin-top: -20px;">
                    <hr class="w-100 {{ $currentStep >= 2 ? 'border-primary' : '' }}">
                </div>
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $currentStep >= 2 ? 'bg-primary text-white' : 'bg-light' }}" style="width: 40px; height: 40px;">
                        @if($currentStep > 2)
                            <i class="bi bi-check-lg"></i>
                        @else
                            2
                        @endif
                    </div>
                    <p class="small mb-0 mt-1 {{ $currentStep >= 2 ? 'text-primary fw-semibold' : 'text-muted' }}">Detectar Campos</p>
                </div>
                <div class="flex-fill d-flex align-items-center" style="margin-top: -20px;">
                    <hr class="w-100 {{ $currentStep >= 3 ? 'border-primary' : '' }}">
                </div>
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $currentStep >= 3 ? 'bg-primary text-white' : 'bg-light' }}" style="width: 40px; height: 40px;">
                        3
                    </div>
                    <p class="small mb-0 mt-1 {{ $currentStep >= 3 ? 'text-primary fw-semibold' : 'text-muted' }}">Mapear Campos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Step 1: Upload PDF --}}
            @if($currentStep === 1)
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-upload me-2"></i>
                            Paso 1: Subir PDF y Datos Basicos
                        </h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit="nextStep">
                            {{-- Nombre y Descripcion --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Nombre de la Plantilla <span class="text-danger">*</span></label>
                                    <input type="text"
                                           wire:model="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="Ej: Modelo EX-03 Cuenta Ajena 2025">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripcion</label>
                                    <textarea wire:model="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="2"
                                              placeholder="Descripcion opcional de la plantilla..."></textarea>
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
                                        <option value="">Seleccionar...</option>
                                        @foreach($documentTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ $document_type ? PdfTemplateType::from($document_type)->description() : '' }}</small>
                                    @error('document_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Solicitud</label>
                                    <select wire:model="application_type" class="form-select @error('application_type') is-invalid @enderror">
                                        <option value="">General (todos los tipos)</option>
                                        @foreach($applicationTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->name }} - {{ Str::limit($type->description(), 40) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Limita esta plantilla a un tipo de solicitud especifico</small>
                                    @error('application_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Archivo PDF --}}
                            <div class="mb-4">
                                <label class="form-label">Archivo PDF <span class="text-danger">*</span></label>
                                <input type="file"
                                       wire:model="file"
                                       class="form-control @error('file') is-invalid @enderror"
                                       accept=".pdf">
                                <small class="text-muted">PDF editable con campos de formulario (AcroForm). Maximo 10MB.</small>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div wire:loading wire:target="file" class="mt-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <span class="text-muted ms-2">Subiendo archivo...</span>
                                </div>

                                @if($file)
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <span>{{ $file->getClientOriginalName() }}</span>
                                        <span class="text-muted ms-2">({{ number_format($file->getSize() / 1024, 2) }} KB)</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Predeterminada --}}
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox"
                                           wire:model="is_default"
                                           class="form-check-input"
                                           id="is_default"
                                           role="switch">
                                    <label class="form-check-label" for="is_default">
                                        <strong>Plantilla Predeterminada</strong>
                                        <br><small class="text-muted">Se usara automaticamente al generar documentos de este tipo</small>
                                    </label>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        Siguiente
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        Procesando...
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

            {{-- Step 2: Detected Fields --}}
            @if($currentStep === 2)
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-list-check me-2"></i>
                            Paso 2: Campos Detectados
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($extractionError)
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Error al extraer campos:</strong>
                                <p class="mb-0 mt-2">{{ $extractionError }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button wire:click="goToStep(1)" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Volver y corregir
                                </button>
                            </div>
                        @elseif(count($extractedFields) === 0)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>No se detectaron campos editables</strong>
                                <p class="mb-0 mt-2">
                                    El PDF no contiene campos de formulario (AcroForm).
                                    Asegurate de usar un PDF con campos editables.
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button wire:click="goToStep(1)" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Volver y cambiar PDF
                                </button>
                                <button wire:click="saveWithoutMapping" class="btn btn-primary">
                                    Guardar sin mapeo
                                </button>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Se detectaron {{ $totalFields }} campos editables</strong>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Campo PDF</th>
                                            <th>Tipo</th>
                                            <th>Obligatorio</th>
                                            <th>Valor por Defecto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($extractedFields as $field)
                                            <tr>
                                                <td><code>{{ $field['name'] }}</code></td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ ucfirst($field['type']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($field['is_required'])
                                                        <i class="bi bi-check text-success"></i>
                                                    @else
                                                        <i class="bi bi-dash text-muted"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($field['value'])
                                                        <small class="text-muted">{{ Str::limit($field['value'], 30) }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2">
                                <button wire:click="goToStep(1)" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Anterior
                                </button>
                                <button wire:click="saveWithoutMapping" class="btn btn-outline-primary">
                                    Guardar sin mapeo
                                </button>
                                <button wire:click="nextStep" class="btn btn-primary">
                                    Configurar Mapeo
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Step 3: Field Mapping --}}
            @if($currentStep === 3)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Paso 3: Mapear Campos
                        </h6>
                        <span class="badge bg-primary">{{ count(array_filter($fieldMapping, fn($m) => !empty($m['field']))) }}/{{ $totalFields }} mapeados</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Asocia cada campo del PDF con un dato del sistema. Los campos no mapeados quedaran vacios.
                        </p>

                        <div class="table-responsive mb-4">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 22%;">Campo PDF</th>
                                        <th style="width: 18%;">Fuente</th>
                                        <th style="width: 25%;">Campo del Sistema</th>
                                        <th style="width: 20%;">Condicion</th>
                                        <th style="width: 15%;">Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($extractedFields as $index => $field)
                                        @php $isCheckbox = ($field['type'] ?? 'text') === 'checkbox'; @endphp
                                        <tr wire:key="mapping-{{ $index }}">
                                            <td>
                                                <code class="small">{{ Str::limit($field['name'], 18) }}</code>
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
                                                <span class="badge bg-{{ $isCheckbox ? 'warning text-dark' : 'light text-dark' }}">
                                                    {{ ucfirst($field['type']) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button wire:click="goToStep(2)" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Anterior
                            </button>
                            <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-lg me-1"></i>
                                    Guardar Plantilla
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informacion
                    </h6>
                </div>
                <div class="card-body small">
                    <h6 class="fw-semibold">Que es una plantilla PDF?</h6>
                    <p class="text-muted mb-3">
                        Una plantilla PDF es un formulario editable (AcroForm) que contiene campos
                        que pueden ser rellenados automaticamente con datos del sistema.
                    </p>

                    <h6 class="fw-semibold">Requisitos del PDF</h6>
                    <ul class="text-muted mb-3">
                        <li>Debe ser un PDF editable (con campos de formulario)</li>
                        <li>Los campos deben tener nombres unicos</li>
                        <li>Formato recomendado: PDF 1.4 o superior</li>
                    </ul>

                    <h6 class="fw-semibold">Mapeo de Campos</h6>
                    <p class="text-muted mb-0">
                        El mapeo conecta los campos del PDF con los datos del sistema
                        (trabajador, empleador, expediente). Puedes configurarlo ahora
                        o mas tarde desde la vista de edicion.
                    </p>
                </div>
            </div>

            {{-- Current File Info --}}
            @if($file)
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            Archivo Seleccionado
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>{{ $file->getClientOriginalName() }}</strong></p>
                        <p class="small text-muted mb-0">
                            Tamaño: {{ number_format($file->getSize() / 1024, 2) }} KB
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
