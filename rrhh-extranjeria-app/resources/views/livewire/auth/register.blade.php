<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.guest')]
#[Title('Registro - RRHH Extranjeria')]
class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $legal_name = '';
    public string $dni = '';
    public string $phone_number = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:100',
            'legal_name' => 'required|string|max:100',
            'dni' => 'required|string|size:9|unique:users,dni',
            'phone_number' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'legal_name.required' => 'El nombre legal es obligatorio.',
            'legal_name.max' => 'El nombre legal no puede superar 100 caracteres.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 9 caracteres.',
            'dni.unique' => 'Este DNI ya esta registrado.',
            'phone_number.required' => 'El telefono es obligatorio.',
            'phone_number.max' => 'El telefono no puede superar 30 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email valido.',
            'email.unique' => 'Este email ya esta registrado.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ];
    }

    public function register(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'legal_name' => $validated['legal_name'],
            'dni' => strtoupper($validated['dni']),
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title text-center mb-4">Crear Cuenta</h4>

            @if (session('error'))
                <div class="alert alert-danger py-2">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="register">
                {{-- Nombre y Apellidos --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Nombre</label>
                        <input type="text"
                               wire:model="first_name"
                               class="form-control @error('first_name') is-invalid @enderror"
                               id="first_name"
                               placeholder="Juan"
                               autofocus>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Apellidos</label>
                        <input type="text"
                               wire:model="last_name"
                               class="form-control @error('last_name') is-invalid @enderror"
                               id="last_name"
                               placeholder="Garcia Lopez">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Nombre Legal --}}
                <div class="mb-3">
                    <label for="legal_name" class="form-label">Nombre Legal Completo</label>
                    <input type="text"
                           wire:model="legal_name"
                           class="form-control @error('legal_name') is-invalid @enderror"
                           id="legal_name"
                           placeholder="Juan Garcia Lopez">
                    <small class="text-muted">Nombre completo tal como aparece en documentos oficiales</small>
                    @error('legal_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DNI y Telefono --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dni" class="form-label">DNI/NIE</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                            <input type="text"
                                   wire:model="dni"
                                   class="form-control @error('dni') is-invalid @enderror"
                                   id="dni"
                                   placeholder="12345678A"
                                   maxlength="9"
                                   style="text-transform: uppercase;">
                        </div>
                        @error('dni')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Telefono</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel"
                                   wire:model="phone_number"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   id="phone_number"
                                   placeholder="+34 600 000 000">
                        </div>
                        @error('phone_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                               wire:model="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               placeholder="tu@email.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contrasenas --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Contrasena</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password"
                                   wire:model="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   placeholder="********">
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Contrasena</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password"
                                   wire:model="password_confirmation"
                                   class="form-control"
                                   id="password_confirmation"
                                   placeholder="********">
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="register">
                            <i class="bi bi-person-plus me-1"></i>
                            Crear Cuenta
                        </span>
                        <span wire:loading wire:target="register">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Creando cuenta...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light text-center py-3">
            <small class="text-muted">
                Ya tienes cuenta?
                <a href="{{ route('login') }}" wire:navigate>Inicia sesion</a>
            </small>
        </div>
    </div>
</div>
