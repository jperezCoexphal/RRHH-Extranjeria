{{-- Sidebar --}}
<nav id="sidebar" class="sidebar">
    {{-- Brand --}}
    <a class="sidebar-brand" href="{{ route('dashboard') }}" wire:navigate>
        <i class="bi bi-globe-americas sidebar-brand-icon"></i>
        <span class="sidebar-brand-text">RRHH Extranjeria</span>
    </a>

    <hr class="sidebar-divider">

    {{-- Nav Items --}}
    <ul class="nav flex-column">
        {{-- Dashboard --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" wire:navigate>
                <i class="bi bi-speedometer2"></i>
                <span class="nav-link-text">Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Gestion</div>

        {{-- Expedientes --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inmigration-files.*') ? 'active' : '' }}" href="{{ route('inmigration-files.index') }}" wire:navigate>
                <i class="bi bi-folder2-open"></i>
                <span class="nav-link-text">Expedientes</span>
            </a>
        </li>

        {{-- Empleadores --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('employers.*') ? 'active' : '' }}" href="{{ route('employers.index') }}" wire:navigate>
                <i class="bi bi-building"></i>
                <span class="nav-link-text">Empleadores</span>
            </a>
        </li>

        {{-- Extranjeros --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('foreigners.*') ? 'active' : '' }}" href="{{ route('foreigners.index') }}" wire:navigate>
                <i class="bi bi-people"></i>
                <span class="nav-link-text">Extranjeros</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Documentos</div>

        {{-- Generacion de Documentos --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}" wire:navigate>
                <i class="bi bi-file-earmark-pdf"></i>
                <span class="nav-link-text">Documentos</span>
            </a>
        </li>

        {{-- Plantillas --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}" wire:navigate>
                <i class="bi bi-file-earmark-ruled"></i>
                <span class="nav-link-text">Plantillas</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Configuracion</div>

        {{-- Plantillas de Requisitos --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('requirement-templates.*') ? 'active' : '' }}" href="{{ route('requirement-templates.index') }}" wire:navigate>
                <i class="bi bi-clipboard-check"></i>
                <span class="nav-link-text">Requisitos</span>
            </a>
        </li>

        {{-- Usuarios --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="#">
                <i class="bi bi-person-gear"></i>
                <span class="nav-link-text">Usuarios</span>
            </a>
        </li>

        {{-- Ajustes --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#">
                <i class="bi bi-gear"></i>
                <span class="nav-link-text">Ajustes</span>
            </a>
        </li>
    </ul>
</nav>
