<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;

new
#[Layout('layouts.app')]
#[Title('Nueva Plantilla - RRHH Extranjeria')]
class extends Component {
    use WithFileUploads;

    public $uploadedFile;
    public string $customName = '';
    public string $category = '';

    public function rules(): array
    {
        return [
            'uploadedFile' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'customName' => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $fileName = $this->customName
            ? $this->customName . '.' . $this->uploadedFile->getClientOriginalExtension()
            : $this->uploadedFile->getClientOriginalName();

        // Asegurar que el nombre es unico
        $path = resource_path('pdf/' . $fileName);
        if (File::exists($path)) {
            $this->addError('uploadedFile', 'Ya existe una plantilla con ese nombre.');
            return;
        }

        $this->uploadedFile->storeAs('', $fileName, [
            'disk' => 'local',
            'path' => resource_path('pdf'),
        ]);

        // Mover el archivo manualmente si storeAs no funciona con path absoluto
        $tempPath = storage_path('app/' . $fileName);
        if (File::exists($tempPath)) {
            File::move($tempPath, $path);
        } else {
            // Alternativa: usar el metodo move del archivo
            $this->uploadedFile->move(resource_path('pdf'), $fileName);
        }

        session()->flash('success', 'Plantilla creada correctamente: ' . $fileName);
        $this->redirect(route('templates.index'), navigate: true);
    }
}; ?>

<div>
    @section('page-title', 'Nueva Plantilla')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}" wire:navigate>Plantillas</a></li>
                    <li class="breadcrumb-item active">Nueva</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Subir Nueva Plantilla</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-cloud-upload me-2"></i>
                        Subir Archivo
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        {{-- Archivo --}}
                        <div class="mb-4">
                            <label class="form-label">Archivo <span class="text-danger">*</span></label>
                            <div class="border rounded p-4 text-center bg-light" style="border-style: dashed !important;">
                                <input type="file"
                                       wire:model="uploadedFile"
                                       class="form-control @error('uploadedFile') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx"
                                       id="fileInput">
                                @error('uploadedFile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    Formatos permitidos: PDF, DOC, DOCX. Tamano maximo: 10MB
                                </small>
                            </div>
                            <div wire:loading wire:target="uploadedFile" class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Subiendo archivo...</p>
                            </div>
                        </div>

                        {{-- Nombre personalizado --}}
                        <div class="mb-4">
                            <label class="form-label">Nombre Personalizado</label>
                            <input type="text"
                                   wire:model="customName"
                                   class="form-control @error('customName') is-invalid @enderror"
                                   placeholder="Dejar vacio para usar el nombre original">
                            @error('customName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Si no especificas un nombre, se usara el nombre original del archivo.
                            </small>
                        </div>

                        {{-- Categoria (informativo) --}}
                        <div class="mb-4">
                            <label class="form-label">Categoria</label>
                            <select wire:model="category" class="form-select">
                                <option value="">Automatica (basada en nombre)</option>
                                <option value="modelos_ex">Modelos EX</option>
                                <option value="memorias">Memorias</option>
                                <option value="contratos">Contratos</option>
                                <option value="otros">Otros</option>
                            </select>
                            <small class="text-muted">
                                La categoria se asigna automaticamente basandose en el nombre del archivo.
                            </small>
                        </div>

                        {{-- Acciones --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar Plantilla
                            </button>
                            <a href="{{ route('templates.index') }}" wire:navigate class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info --}}
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i>Informacion</h6>
                    <p class="small text-muted mb-2">
                        Las plantillas se utilizan como base para generar los documentos de extranjeria.
                    </p>
                    <ul class="small text-muted mb-0">
                        <li><strong>Modelos EX:</strong> Nombra el archivo empezando con "EX" (ej: EX03_custom.pdf)</li>
                        <li><strong>Memorias:</strong> Incluye "memoria" en el nombre (ej: Memoria_2025.pdf)</li>
                        <li><strong>Contratos:</strong> Incluye "contrato" en el nombre (ej: Contrato_Temporal.pdf)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
