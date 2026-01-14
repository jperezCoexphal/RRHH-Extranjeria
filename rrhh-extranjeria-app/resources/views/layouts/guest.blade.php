<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RRHH Extranjeria')</title>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    {{-- Livewire Styles --}}
    @livewireStyles
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    {{-- Logo --}}
                    <div class="text-center mb-4">
                        <h1 class="h3 text-primary fw-bold">
                            <i class="bi bi-building me-2"></i>
                            RRHH Extranjeria
                        </h1>
                        <p class="text-muted">Sistema de Gestion de Expedientes</p>
                    </div>

                    {{-- Alerts --}}
                    @if (session('status'))
                        <div class="alert alert-success mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Content --}}
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>
