<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.guest')]
#[Title('Iniciar Sesion - RRHH Extranjeria')]
class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Introduce un email valido.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
        ];
    }

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            session()->flash('error', "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.");
            return;
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey);
            session()->flash('error', 'Las credenciales no coinciden con nuestros registros.');
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title text-center mb-4">Iniciar Sesion</h4>

            @if (session('error'))
                <div class="alert alert-danger py-2">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success py-2">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit="login">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                               wire:model="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               placeholder="tu@email.com"
                               autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
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

                <div class="mb-3 form-check">
                    <input type="checkbox"
                           wire:model="remember"
                           class="form-check-input"
                           id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Iniciar Sesion
                        </span>
                        <span wire:loading wire:target="login">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Verificando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light text-center py-3">
            <small class="text-muted">
                No tienes cuenta?
                <a href="{{ route('register') }}" wire:navigate>Registrate aqui</a>
            </small>
        </div>
    </div>

    <div class="text-center mt-4">
        <small class="text-muted">
            Usuario demo: <strong>admin@rrhh-extranjeria.com</strong> / <strong>password</strong>
        </small>
    </div>
</div>
