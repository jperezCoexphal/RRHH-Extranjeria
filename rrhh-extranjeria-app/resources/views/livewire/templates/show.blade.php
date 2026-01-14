<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\File;

new
#[Layout('layouts.app')]
#[Title('Detalle Plantilla - RRHH Extranjeria')]
class extends Component {
    public string $fileName = '';
    public array $template = [];

    public function mount(string $id): void
    {
        $this->fileName = urldecode($id);
        $this->loadTemplate();
    }

    protected function loadTemplate(): void
    {
        $path = resource_path('pdf/' . $this->fileName);

        if (!File::exists($path)) {
            session()->flash('error', 'Plantilla no encontrada.');
            $this->redirect(route('templates.index'), navigate: true);
            return;
        }

        $file = new \SplFileInfo($path);

        // Categorizar
        $category = 'Otros';
        if (str_starts_with($this->fileName, 'EX')) {
            $category = 'Modelos EX';
        } elseif (str_contains(strtolower($this->fileName), 'memoria')) {
            $category = 'Memorias';
        } elseif (str_contains(strtolower($this->fileName), 'contrato')) {
            $category = 'Contratos';
        }

        // Descripcion basada en el nombre
        $description = $this->getDescription();

        $this->template = [
            'name' => $this->fileName,
            'extension' => strtoupper($file->getExtension()),
            'size' => $file->getSize(),
            'modified' => $file->getMTime(),
            'category' => $category,
            'description' => $description,
            'path' => $path,
        ];
    }

    protected function getDescription(): string
    {
        $descriptions = [
            'EX03' => 'Formulario de autorización de residencia temporal y trabajo por cuenta ajena o autorización de trabajo por cuenta ajena.',
            'EX10' => 'Formulario de autorización de residencia por circunstancias excepcionales (arraigo social, laboral, familiar).',
            'EX26' => 'Formulario de modificación de autorización de residencia o estancia.',
            'Memoria' => 'Documento justificativo de la necesidad de contratación del trabajador extranjero.',
            'Contrato' => 'Modelo de contrato de trabajo para trabajadores extranjeros.',
        ];

        foreach ($descriptions as $key => $desc) {
            if (str_contains(strtolower($this->fileName), strtolower($key))) {
                return $desc;
            }
        }

        return 'Plantilla de documento para trámites de extranjería.';
    }

    public function delete(): void
    {
        $path = resource_path('pdf/' . $this->fileName);
        if (File::exists($path)) {
            File::delete($path);
            session()->flash('success', 'Plantilla eliminada correctamente.');
            $this->redirect(route('templates.index'), navigate: true);
        } else {
            session()->flash('error', 'Plantilla no encontrada.');
        }
    }

    public function download(): mixed
    {
        $path = resource_path('pdf/' . $this->fileName);
        if (File::exists($path)) {
            return response()->download($path);
        }
        session()->flash('error', 'Plantilla no encontrada.');
        return null;
    }
}; ?>

<div>
    @section('page-title', 'Plantilla - ' . $template['name'])

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}" wire:navigate>Plantillas</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($template['name'], 30) }}</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Detalle de Plantilla</h4>
        </div>
        <div class="btn-group">
            <a href="{{ route('templates.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Info Principal --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        @if($template['extension'] === 'PDF')
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                        @elseif(in_array($template['extension'], ['DOC', 'DOCX']))
                            <i class="bi bi-file-earmark-word text-primary me-2"></i>
                        @else
                            <i class="bi bi-file-earmark me-2"></i>
                        @endif
                        {{ $template['name'] }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-0">Categoria</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ $template['category'] }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-0">Formato</label>
                            <p class="mb-0">{{ $template['extension'] }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-0">Tamano</label>
                            <p class="mb-0">{{ number_format($template['size'] / 1024, 2) }} KB</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-0">Ultima Modificacion</label>
                            <p class="mb-0">{{ date('d/m/Y H:i', $template['modified']) }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted mb-0">Descripcion</label>
                            <p class="mb-0">{{ $template['description'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview (solo para PDF) --}}
            @if($template['extension'] === 'PDF')
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="bi bi-eye me-2"></i>
                            Vista Previa
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9" style="min-height: 600px;">
                            <iframe src="{{ route('templates.file', $template['name']) }}"
                                    style="border: none;">
                                Tu navegador no soporta la vista previa de PDF.
                            </iframe>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-file-earmark-word text-primary" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3 mb-0">Vista previa no disponible para archivos Word</p>
                        <small class="text-muted">Descarga el archivo para ver su contenido</small>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Acciones --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('templates.file', $template['name']) }}"
                           class="btn btn-primary"
                           download="{{ $template['name'] }}">
                            <i class="bi bi-download me-1"></i>
                            Descargar
                        </a>
                        <button wire:click="delete"
                                wire:confirm="¿Seguro que deseas eliminar esta plantilla? Esta accion no se puede deshacer."
                                class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Uso --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informacion de Uso
                    </h6>
                </div>
                <div class="card-body">
                    @if($template['category'] === 'Modelos EX')
                        <p class="small mb-2">
                            <strong>Tipo de Documento:</strong> Formulario Oficial
                        </p>
                        <p class="small mb-0">
                            Este formulario se utiliza para solicitudes de autorizacion ante la Oficina de Extranjeria.
                            Se rellena automaticamente con los datos del expediente.
                        </p>
                    @elseif($template['category'] === 'Memorias')
                        <p class="small mb-2">
                            <strong>Tipo de Documento:</strong> Documento Justificativo
                        </p>
                        <p class="small mb-0">
                            La memoria justificativa explica la necesidad de contratar al trabajador extranjero.
                            Incluye informacion de la empresa y el puesto de trabajo.
                        </p>
                    @elseif($template['category'] === 'Contratos')
                        <p class="small mb-2">
                            <strong>Tipo de Documento:</strong> Contrato Laboral
                        </p>
                        <p class="small mb-0">
                            El contrato de trabajo se genera con los datos del empleador y trabajador.
                            Cumple con los requisitos del Reglamento de Extranjeria.
                        </p>
                    @else
                        <p class="small mb-0">
                            Plantilla generica para tramites de extranjeria.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
