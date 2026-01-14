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
<body>
    <div id="app-wrapper">
        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Content Wrapper --}}
        <div id="contentWrapper" class="content-wrapper">
            {{-- Topbar --}}
            @include('layouts.partials.topbar')

            {{-- Main Content --}}
            <main class="main-content">
                {{-- Alerts --}}
                @include('layouts.partials.alerts')

                {{-- Page Content --}}
                @yield('content')

                {{-- Livewire Slot --}}
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>
