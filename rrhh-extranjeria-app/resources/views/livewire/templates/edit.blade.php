<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;

new
#[Layout('layouts.app')]
#[Title('Editar Plantilla - RRHH Extranjeria')]
class extends Component {
    use WithFileUploads;

    public string $fileName = '';
    public array $template = [];
    public $newFile;
    public string $newName = '';

    public function mount(string $id): void
    {
        $this->fileName = urldecode($id);
        $this->newName = pathinfo($this->fileName, PATHINFO_FILENAME);
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

        $this->template = [
            'name' => $this->fileName,
            'extension' => strtoupper($file->getExtension()),
            'size' => $file->getSize(),
            'modified' => $file->getMTime(),
            'path' => $path,
        ];
    }

    public function rules(): array
    {
        return [
            'newFile' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'newName' => 'required|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $oldPath = resource_path('pdf/' . $this->fileName);
        $extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $newFileName = $this->newName . '.' . $extension;
        $newPath = resource_path('pdf/' . $newFileName);

        // Si hay nuevo archivo, reemplazar
        if ($this->newFile) {
            // Usar la extension del nuevo archivo
            $extension = $this->newFile->getClientOriginalExtension();
            $newFileName = $this->newName . '.' . $extension;
            $newPath = resource_path('pdf/' . $newFileName);

            // Eliminar archivo anterior
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }

            // Guardar nuevo archivo
            $this->newFile->move(resource_path('pdf'), $newFileName);
        } else {
            // Solo renombrar si el nombre cambio
            if ($oldPath !== $newPath) {
                if (File::exists($newPath)) {
                    $this->addError('newName', 'Ya existe una plantilla con ese nombre.');
                    return;
                }
                File::move($oldPath, $newPath);
            }
        }

        session()->flash('success', 'Plantilla actualizada correctamente.');
        $this->redirect(route('templates.show', urlencode($newFileName)), navigate: true);
    }
}; ?>

<div>
    @section('page-title', 'Editar Plantilla')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}" wire:navigate>Plantillas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('templates.show', urlencode($fileName)) }}" wire:navigate>{{ Str::limit($fileName, 20) }}</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Editar Plantilla</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-pencil me-2"></i>
                        Modificar Plantilla
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        {{-- Info actual --}}
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                @if($template['extension'] === 'PDF')
                                    <i class="bi bi-file-earmark-pdf text-danger fs-3 me-3"></i>
                                @else
                                    <i class="bi bi-file-earmark-word text-primary fs-3 me-3"></i>
                                @endif
                                <div>
                                    <strong>{{ $template['name'] }}</strong>
                                    <br>
                                    <small>
                                        {{ $template['extension'] }} -
                                        {{ number_format($template['size'] / 1024, 2) }} KB -
                                        Modificado: {{ date('d/m/Y H:i', $template['modified']) }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Nombre --}}
                        <div class="mb-4">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       wire:model="newName"
                                       class="form-control @error('newName') is-invalid @enderror">
                                <span class="input-group-text">.{{ strtolower($template['extension']) }}</span>
                            </div>
                            @error('newName')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Reemplazar archivo --}}
                        <div class="mb-4">
                            <label class="form-label">Reemplazar Archivo</label>
                            <input type="file"
                                   wire:model="newFile"
                                   class="form-control @error('newFile') is-invalid @enderror"
                                   accept=".pdf,.doc,.docx">
                            @error('newFile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Opcional. Selecciona un nuevo archivo para reemplazar el actual.
                            </small>
                            <div wire:loading wire:target="newFile" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <span class="text-muted ms-2">Subiendo archivo...</span>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar Cambios
                            </button>
                            <a href="{{ route('templates.show', urlencode($fileName)) }}" wire:navigate class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Ayuda
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        <strong>Renombrar:</strong> Cambia el nombre del archivo. La extension se mantendra.
                    </p>
                    <p class="small text-muted mb-0">
                        <strong>Reemplazar:</strong> Sube un nuevo archivo para sustituir el actual.
                        El nuevo archivo puede tener una extension diferente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
