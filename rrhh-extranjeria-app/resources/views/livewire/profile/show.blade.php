<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Mi Perfil - RRHH Extranjeria')]
class extends Component {

    // Datos personales
    public string $first_name = '';
    public string $last_name = '';
    public string $legal_name = '';
    public string $dni = '';
    public string $phone_number = '';
    public string $email = '';

    // Cambio de contraseña
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->legal_name = $user->legal_name ?? '';
        $this->dni = $user->dni ?? '';
        $this->phone_number = $user->phone_number ?? '';
        $this->email = $user->email ?? '';
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:100',
            'legal_name' => 'nullable|string|max:100',
            'dni' => ['nullable', 'string', 'max:9', Rule::unique('users', 'dni')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:30',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'dni.unique' => 'Este DNI ya esta registrado.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es valido.',
            'email.unique' => 'Este email ya esta registrado.',
        ]);

        $user->update($validated);

        session()->flash('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'new_password.required' => 'La nueva contraseña es obligatoria.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success', 'Contraseña actualizada correctamente.');
    }

    public function deleteAccount(): void
    {
        $user = auth()->user();

        Auth::logout();

        $user->delete();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirect(route('login'), navigate: true);
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'filesCount' => $user->inmigrationFiles()->count(),
        ];
    }
}; ?>

<div>
    @section('page-title', 'Mi Perfil')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </nav>
            <h4 class="text-gray-800 mb-0">Mi Perfil</h4>
        </div>
    </div>

    <div class="row">
        {{-- Columna principal --}}
        <div class="col-lg-8">

            {{-- Card: Informacion Personal --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-person me-2"></i>
                        Informacion Personal
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="updateProfile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       placeholder="Nombre">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       placeholder="Apellidos">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Razon Social</label>
                                <input type="text"
                                       wire:model="legal_name"
                                       class="form-control @error('legal_name') is-invalid @enderror"
                                       placeholder="Razon social o nombre comercial">
                                @error('legal_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DNI</label>
                                <input type="text"
                                       wire:model="dni"
                                       class="form-control @error('dni') is-invalid @enderror"
                                       placeholder="12345678A"
                                       maxlength="9">
                                @error('dni')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefono</label>
                                <input type="text"
                                       wire:model="phone_number"
                                       class="form-control @error('phone_number') is-invalid @enderror"
                                       placeholder="+34 600 000 000">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       wire:model="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="correo@ejemplo.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateProfile">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Guardar Cambios
                                </span>
                                <span wire:loading wire:target="updateProfile">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card: Cambiar Contraseña --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        Cambiar Contraseña
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="updatePassword">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                                <input type="password"
                                       wire:model="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       placeholder="Introduce tu contraseña actual">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                                <input type="password"
                                       wire:model="new_password"
                                       class="form-control @error('new_password') is-invalid @enderror"
                                       placeholder="Minimo 8 caracteres">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input type="password"
                                       wire:model="new_password_confirmation"
                                       class="form-control"
                                       placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePassword">
                                    <i class="bi bi-key me-1"></i>
                                    Actualizar Contraseña
                                </span>
                                <span wire:loading wire:target="updatePassword">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Actualizando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card: Zona Peligrosa --}}
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="m-0 text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Zona Peligrosa
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Al eliminar tu cuenta se desactivara tu acceso al sistema. Esta accion no se puede deshacer facilmente.
                        Los expedientes asignados a tu usuario no seran eliminados.
                    </p>
                    <button type="button"
                            class="btn btn-outline-danger"
                            onclick="confirm('¿Estas seguro de que quieres eliminar tu cuenta? Esta accion no se puede deshacer.') && @this.call('deleteAccount')">
                        <i class="bi bi-trash me-1"></i>
                        Eliminar mi Cuenta
                    </button>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- Card: Resumen del usuario --}}
            <div class="card mb-4">
                <div class="card-body text-center">
                    {{-- Avatar --}}
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3"
                         style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">
                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                    </div>

                    <h5 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <hr>

                    <div class="text-start">
                        @if($user->dni)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">DNI</span>
                                <span class="fw-semibold">{{ $user->dni }}</span>
                            </div>
                        @endif
                        @if($user->phone_number)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Telefono</span>
                                <span class="fw-semibold">{{ $user->phone_number }}</span>
                            </div>
                        @endif
                        @if($user->legal_name)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Razon Social</span>
                                <span class="fw-semibold">{{ $user->legal_name }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Expedientes asignados</span>
                            <span class="badge bg-primary">{{ $filesCount }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Registrado desde</span>
                            <span class="fw-semibold">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
