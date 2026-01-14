<?php

use App\Models\Foreigner;
use App\Models\Country;
use App\Enums\Gender;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new
#[Layout('layouts.app')]
#[Title('Extranjeros - RRHH Extranjeria')]
class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    #[Url]
    public string $nationality = '';

    #[Url]
    public string $gender = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'nationality', 'gender']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $foreigner = Foreigner::find($id);
        if ($foreigner) {
            $foreigner->delete();
            session()->flash('success', 'Extranjero eliminado correctamente.');
        }
    }

    public function with(): array
    {
        $query = Foreigner::with(['nationality', 'birthCountry']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('passport', 'like', "%{$this->search}%")
                  ->orWhere('nie', 'like', "%{$this->search}%");
            });
        }

        if ($this->nationality) {
            $query->where('nationality_id', $this->nationality);
        }

        if ($this->gender) {
            $query->where('gender', $this->gender);
        }

        return [
            'foreigners' => $query->orderBy('created_at', 'desc')->paginate(10),
            'countries' => Country::orderBy('country_name')->get(),
            'genders' => Gender::cases(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Extranjeros')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-gray-800 mb-1">Gestion de Extranjeros</h4>
            <p class="text-muted mb-0">Listado de trabajadores extranjeros</p>
        </div>
        <a href="{{ route('foreigners.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo Extranjero
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
                               placeholder="Nombre, NIE, Pasaporte...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Nacionalidad</label>
                    <select wire:model.live="nationality" class="form-select">
                        <option value="">Todas</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Genero</label>
                    <select wire:model.live="gender" class="form-select">
                        <option value="">Todos</option>
                        @foreach($genders as $g)
                            <option value="{{ $g->value }}">{{ $g->value }}</option>
                        @endforeach
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
                            <th>Nombre Completo</th>
                            <th>NIE</th>
                            <th>Pasaporte</th>
                            <th>Nacionalidad</th>
                            <th>Fecha Nac.</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foreigners as $foreigner)
                            <tr wire:key="foreigner-{{ $foreigner->id }}">
                                <td>
                                    <a href="{{ route('foreigners.show', $foreigner->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $foreigner->first_name }} {{ $foreigner->last_name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-{{ $foreigner->gender->value === 'Masculino' ? 'gender-male' : 'gender-female' }} me-1"></i>
                                        {{ $foreigner->gender->value }}
                                    </small>
                                </td>
                                <td><code>{{ $foreigner->nie }}</code></td>
                                <td><code>{{ $foreigner->passport }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $foreigner->nationality?->country_name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $foreigner->birthdate?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('foreigners.show', $foreigner->id) }}" class="btn btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('foreigners.edit', $foreigner->id) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="delete({{ $foreigner->id }})"
                                                wire:confirm="¿Estas seguro de eliminar este extranjero?"
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
                                    <i class="bi bi-people text-gray-300" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No se encontraron extranjeros</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($foreigners->hasPages())
            <div class="card-footer">
                {{ $foreigners->links() }}
            </div>
        @endif
    </div>
</div>
