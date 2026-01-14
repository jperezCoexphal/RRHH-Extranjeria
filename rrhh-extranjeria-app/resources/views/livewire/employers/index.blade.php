<?php

use App\Models\Employer;
use App\Enums\LegalForm;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Empleadores - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $legalForm = '';

    #[Url]
    public string $associated = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLegalForm(): void
    {
        $this->resetPage();
    }

    public function updatingAssociated(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'legalForm', 'associated']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $employer = Employer::find($id);
        if ($employer) {
            $employer->delete();
            session()->flash('success', 'Empleador eliminado correctamente.');
        }
    }

    public function with(): array
    {
        $query = Employer::with(['company', 'freelancer']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('comercial_name', 'like', "%{$this->search}%")
                  ->orWhere('fiscal_name', 'like', "%{$this->search}%")
                  ->orWhere('nif', 'like', "%{$this->search}%");
            });
        }

        if ($this->legalForm) {
            $query->where('legal_form', $this->legalForm);
        }

        if ($this->associated !== '') {
            $query->where('is_associated', $this->associated === '1');
        }

        return [
            'employers' => $query->orderBy('created_at', 'desc')->paginate(10),
            'legalForms' => LegalForm::cases(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Empleadores')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Gestion de Empleadores</h4>
            <p class="text-muted mb-0">Listado de empresas y autonomos</p>
        </div>
        <a href="{{ route('employers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo Empleador
        </a>
    </div>

    {{-- Filters Card --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0">
                <i class="bi bi-funnel me-2"></i>
                Filtros
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Nombre, NIF...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Forma Juridica</label>
                    <select wire:model.live="legalForm" class="form-select">
                        <option value="">Todas</option>
                        @foreach($legalForms as $form)
                            <option value="{{ $form->value }}">{{ $form->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Asociado</label>
                    <select wire:model.live="associated" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Si</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre Comercial</th>
                            <th>NIF</th>
                            <th>Forma Juridica</th>
                            <th>Telefono</th>
                            <th>Asociado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employers as $employer)
                            <tr wire:key="employer-{{ $employer->id }}">
                                <td>
                                    <a href="{{ route('employers.show', $employer->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $employer->comercial_name }}
                                    </a>
                                    @if($employer->fiscal_name && $employer->fiscal_name !== $employer->comercial_name)
                                        <br><small class="text-muted">{{ $employer->fiscal_name }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $employer->nif }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $employer->legal_form->name }}</span>
                                </td>
                                <td>{{ $employer->phone ?? '-' }}</td>
                                <td>
                                    @if($employer->is_associated)
                                        <span class="badge bg-success">Si</span>
                                    @else
                                        <span class="badge bg-light text-dark">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('employers.show', $employer->id) }}" class="btn btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('employers.edit', $employer->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="delete({{ $employer->id }})"
                                                wire:confirm="¿Estas seguro de eliminar este empleador?"
                                                class="btn btn-outline-danger"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-building text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No se encontraron empleadores</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employers->hasPages())
            <div class="card-footer">
                {{ $employers->links() }}
            </div>
        @endif
    </div>
</div>
