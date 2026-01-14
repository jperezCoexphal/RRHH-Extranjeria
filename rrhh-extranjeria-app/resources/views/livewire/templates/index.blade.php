<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;

new
#[Layout('layouts.app')]
#[Title('Plantillas - RRHH Extranjeria')]
class extends Component {
    use WithFileUploads;

    public $uploadedFile;
    public string $search = '';

    public function rules(): array
    {
        return [
            'uploadedFile' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function upload(): void
    {
        $this->validate();

        $fileName = $this->uploadedFile->getClientOriginalName();
        $this->uploadedFile->storeAs('pdf', $fileName, 'resources');

        session()->flash('success', 'Plantilla subida correctamente: ' . $fileName);
        $this->uploadedFile = null;
    }

    public function delete(string $fileName): void
    {
        $path = resource_path('pdf/' . $fileName);
        if (File::exists($path)) {
            File::delete($path);
            session()->flash('success', 'Plantilla eliminada correctamente.');
        } else {
            session()->flash('error', 'Plantilla no encontrada.');
        }
    }

    public function download(string $fileName): mixed
    {
        $path = resource_path('pdf/' . $fileName);
        if (File::exists($path)) {
            return response()->download($path);
        }
        session()->flash('error', 'Plantilla no encontrada.');
        return null;
    }

    public function with(): array
    {
        $templatesPath = resource_path('pdf');
        $templates = [];

        if (File::isDirectory($templatesPath)) {
            $files = File::files($templatesPath);
            foreach ($files as $file) {
                $fileName = $file->getFilename();
                $extension = $file->getExtension();

                // Filtro de busqueda
                if ($this->search && !str_contains(strtolower($fileName), strtolower($this->search))) {
                    continue;
                }

                // Categorizar plantillas
                $category = 'Otros';
                if (str_starts_with($fileName, 'EX')) {
                    $category = 'Modelos EX';
                } elseif (str_contains(strtolower($fileName), 'memoria')) {
                    $category = 'Memorias';
                } elseif (str_contains(strtolower($fileName), 'contrato')) {
                    $category = 'Contratos';
                }

                $templates[] = [
                    'name' => $fileName,
                    'extension' => strtoupper($extension),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'category' => $category,
                ];
            }
        }

        // Agrupar por categoria
        $grouped = collect($templates)->groupBy('category');

        return [
            'templates' => $templates,
            'groupedTemplates' => $grouped,
            'totalTemplates' => count($templates),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Plantillas')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Gestion de Plantillas</h4>
            <p class="text-muted mb-0">Plantillas base para la generacion de documentos</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-1"></i>
            Subir Plantilla
        </button>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalTemplates }}</h3>
                            <small>Total Plantillas</small>
                        </div>
                        <i class="bi bi-file-earmark-ruled fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        @foreach($groupedTemplates as $category => $items)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ count($items) }}</h3>
                                <small class="text-muted">{{ $category }}</small>
                            </div>
                            @if($category === 'Modelos EX')
                                <i class="bi bi-file-earmark-text fs-1 text-primary opacity-50"></i>
                            @elseif($category === 'Memorias')
                                <i class="bi bi-file-earmark-richtext fs-1 text-info opacity-50"></i>
                            @elseif($category === 'Contratos')
                                <i class="bi bi-file-earmark-medical fs-1 text-success opacity-50"></i>
                            @else
                                <i class="bi bi-file-earmark fs-1 text-secondary opacity-50"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control"
                       placeholder="Buscar plantilla...">
            </div>
        </div>
    </div>

    {{-- Templates by Category --}}
    @forelse($groupedTemplates as $category => $items)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0">
                    @if($category === 'Modelos EX')
                        <i class="bi bi-file-earmark-text me-2"></i>
                    @elseif($category === 'Memorias')
                        <i class="bi bi-file-earmark-richtext me-2"></i>
                    @elseif($category === 'Contratos')
                        <i class="bi bi-file-earmark-medical me-2"></i>
                    @else
                        <i class="bi bi-file-earmark me-2"></i>
                    @endif
                    {{ $category }}
                    <span class="badge bg-secondary ms-2">{{ count($items) }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($items as $template)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                @if($template['extension'] === 'PDF')
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-3"></i>
                                @elseif(in_array($template['extension'], ['DOC', 'DOCX']))
                                    <i class="bi bi-file-earmark-word text-primary fs-4 me-3"></i>
                                @else
                                    <i class="bi bi-file-earmark fs-4 me-3"></i>
                                @endif
                                <div>
                                    <strong>{{ $template['name'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $template['extension'] }} -
                                        {{ number_format($template['size'] / 1024, 2) }} KB -
                                        {{ date('d/m/Y H:i', $template['modified']) }}
                                    </small>
                                </div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('templates.show', urlencode($template['name'])) }}"
                                   wire:navigate
                                   class="btn btn-outline-primary"
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('templates.file', $template['name']) }}"
                                   class="btn btn-outline-success"
                                   title="Descargar"
                                   download="{{ $template['name'] }}">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button wire:click="delete('{{ $template['name'] }}')"
                                        wire:confirm="¿Seguro que deseas eliminar esta plantilla?"
                                        class="btn btn-outline-danger"
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-x text-gray-300" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No se encontraron plantillas</p>
                @if($search)
                    <small class="text-muted">Intenta con otro termino de busqueda</small>
                @else
                    <small class="text-muted">Sube plantillas usando el boton "Subir Plantilla"</small>
                @endif
            </div>
        </div>
    @endforelse

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="upload">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cloud-upload me-2"></i>
                            Subir Plantilla
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Archivo</label>
                            <input type="file"
                                   wire:model="uploadedFile"
                                   class="form-control @error('uploadedFile') is-invalid @enderror"
                                   accept=".pdf,.doc,.docx">
                            @error('uploadedFile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX. Maximo 10MB.</small>
                        </div>
                        <div wire:loading wire:target="uploadedFile" class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted mt-2 mb-0">Subiendo archivo...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <i class="bi bi-check-lg me-1"></i>
                            Subir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
