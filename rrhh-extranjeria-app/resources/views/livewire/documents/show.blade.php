<?php

use App\Models\InmigrationFile;
use App\Services\DocumentGenerationService;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app')]
#[Title('Documentos del Expediente - RRHH Extranjeria')]
class extends Component {
    public InmigrationFile $file;
    public array $documents = [];
    public array $availability = [];
    public bool $loading = false;

    public function mount(InmigrationFile $inmigrationFileId): void
    {
        $this->file = $inmigrationFileId->load(['employer', 'foreigner', 'workAddress']);
        $this->loadDocuments();
        $this->checkAvailability();
    }

    public function loadDocuments(): void
    {
        $path = "documents/{$this->file->id}";
        if (Storage::disk('local')->exists($path)) {
            $files = Storage::disk('local')->files($path);
            $this->documents = collect($files)->map(function ($file) {
                return [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'modified' => Storage::disk('local')->lastModified($file),
                ];
            })->toArray();
        }
    }

    public function checkAvailability(): void
    {
        $service = app(DocumentGenerationService::class);
        $this->availability = $service->checkAvailability($this->file);
    }

    public function generateAll(): void
    {
        $this->loading = true;

        try {
            $service = app(DocumentGenerationService::class);
            $result = $service->generateDocumentPack($this->file);

            if ($result['success']) {
                session()->flash('success', 'Documentos generados correctamente: ' . implode(', ', $result['generated']));
            } else {
                session()->flash('error', 'Error al generar documentos: ' . implode(', ', $result['errors']));
            }

            $this->loadDocuments();
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }

        $this->loading = false;
    }

    public function generateModeloEX(): void
    {
        $this->loading = true;

        try {
            $service = app(DocumentGenerationService::class);
            $result = $service->generateModeloEX($this->file);

            if ($result) {
                session()->flash('success', 'Modelo EX generado correctamente.');
            } else {
                session()->flash('error', 'Error al generar el Modelo EX.');
            }

            $this->loadDocuments();
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }

        $this->loading = false;
    }

    public function downloadDocument(string $documentName): mixed
    {
        $path = "documents/{$this->file->id}/{$documentName}";
        if (Storage::disk('local')->exists($path)) {
            return response()->download(Storage::disk('local')->path($path));
        }
        session()->flash('error', 'Documento no encontrado.');
        return null;
    }

    public function deleteDocument(string $documentName): void
    {
        $path = "documents/{$this->file->id}/{$documentName}";
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            session()->flash('success', 'Documento eliminado correctamente.');
            $this->loadDocuments();
        } else {
            session()->flash('error', 'Documento no encontrado.');
        }
    }

    public function with(): array
    {
        return [
            'expediente' => $this->file,
        ];
    }
}; ?>

<div>
    @section('page-title', 'Documentos - ' . $expediente->file_code)

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" wire:navigate>Documentos</a></li>
                    <li class="breadcrumb-item active">{{ $expediente->file_code }}</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">{{ $expediente->file_title }}</h4>
        </div>
        <div>
            <a href="{{ route('inmigration-files.show', $expediente->id) }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-folder2-open me-1"></i>
                Ver Expediente
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Generacion de Documentos --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Generar Documentos
                    </h6>
                    <span wire:loading class="spinner-border spinner-border-sm text-primary"></span>
                </div>
                <div class="card-body">
                    {{-- Disponibilidad --}}
                    <div class="mb-4">
                        <h6 class="small text-muted mb-3">Datos Disponibles para Generacion</h6>
                        <div class="row g-2">
                            @foreach($availability as $key => $available)
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        @if($available)
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        @else
                                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                        @endif
                                        <span class="small">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    {{-- Botones de generacion --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <button wire:click="generateAll"
                                wire:loading.attr="disabled"
                                class="btn btn-primary"
                                @if(!($availability['employer'] ?? false) || !($availability['foreigner'] ?? false)) disabled @endif>
                            <i class="bi bi-file-earmark-plus me-1"></i>
                            Generar Pack Completo
                        </button>
                        <button wire:click="generateModeloEX"
                                wire:loading.attr="disabled"
                                class="btn btn-outline-primary"
                                @if(!($availability['employer'] ?? false) || !($availability['foreigner'] ?? false)) disabled @endif>
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Solo Modelo EX
                        </button>
                    </div>

                    @if(!($availability['employer'] ?? false) || !($availability['foreigner'] ?? false))
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Faltan datos obligatorios para generar documentos. Completa el empleador y trabajador del expediente.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Lista de Documentos Generados --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-files me-2"></i>
                        Documentos Generados
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(count($documents) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($documents as $doc)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <strong>{{ $doc['name'] }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ number_format($doc['size'] / 1024, 2) }} KB -
                                            {{ date('d/m/Y H:i', $doc['modified']) }}
                                        </small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('documents.download-by-name', [$expediente->id, $doc['name']]) }}"
                                           class="btn btn-outline-primary"
                                           title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button wire:click="deleteDocument('{{ $doc['name'] }}')"
                                                wire:confirm="¿Seguro que deseas eliminar este documento?"
                                                class="btn btn-outline-danger"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('documents.download-all', $expediente->id) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-download me-1"></i>
                                Descargar Todo (ZIP)
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-x text-gray-300" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No hay documentos generados</p>
                            <small class="text-muted">Usa los botones de arriba para generar documentos</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Info Expediente --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-folder2-open me-2"></i>
                        Informacion del Expediente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Codigo</label>
                        <p class="mb-0 fw-bold">{{ $expediente->file_code }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Tipo Solicitud</label>
                        <p class="mb-0">{{ $expediente->application_type->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Estado</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $expediente->status->value === 'favorable' ? 'success' : ($expediente->status->value === 'presentado' ? 'primary' : 'info') }}">
                                {{ ucfirst(str_replace('_', ' ', $expediente->status->value)) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Empleador --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-building me-2"></i>
                        Empleador
                    </h6>
                </div>
                <div class="card-body">
                    @if($expediente->employer)
                        <p class="mb-1 fw-bold">{{ $expediente->employer->comercial_name }}</p>
                        <p class="mb-0 small text-muted">{{ $expediente->employer->nif }}</p>
                    @else
                        <p class="text-muted mb-0">No asignado</p>
                    @endif
                </div>
            </div>

            {{-- Info Trabajador --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-person me-2"></i>
                        Trabajador
                    </h6>
                </div>
                <div class="card-body">
                    @if($expediente->foreigner)
                        <p class="mb-1 fw-bold">{{ $expediente->foreigner->first_name }} {{ $expediente->foreigner->last_name }}</p>
                        <p class="mb-0 small text-muted">{{ $expediente->foreigner->nie }}</p>
                    @else
                        <p class="text-muted mb-0">No asignado</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
