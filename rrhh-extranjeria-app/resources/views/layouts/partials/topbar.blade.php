{{-- Topbar --}}
<header class="topbar">
    {{-- Left Side --}}
    <div class="topbar-left">
        {{-- Sidebar Toggle --}}
        <button id="sidebarToggle" class="sidebar-toggle" type="button">
            <i class="bi bi-list"></i>
        </button>

        {{-- Page Title --}}
        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
    </div>

    {{-- Right Side --}}
    <div class="topbar-right">
        {{-- Notifications --}}
        <div class="dropdown">
            <button class="btn btn-link text-secondary position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header">Centro de Notificaciones</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="bg-primary rounded-circle p-2 text-white">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">Diciembre 12, 2024</div>
                            <span>Nuevo expediente creado</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="bg-success rounded-circle p-2 text-white">
                                <i class="bi bi-check-lg"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">Diciembre 11, 2024</div>
                            <span>Expediente EX-001 aprobado</span>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center small text-gray-500" href="#">
                        Ver todas las notificaciones
                    </a>
                </li>
            </ul>
        </div>

        {{-- Messages --}}
        <div class="dropdown">
            <button class="btn btn-link text-secondary position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-envelope fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                    7
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header">Centro de Mensajes</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center small text-gray-500" href="#">
                        Ver todos los mensajes
                    </a>
                </li>
            </ul>
        </div>

        <div class="topbar-divider"></div>

        {{-- User Dropdown --}}
        <div class="dropdown user-dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <span class="d-none d-lg-inline me-2">
                    @auth
                        {{ auth()->user()->first_name ?? auth()->user()->name ?? 'Usuario' }}
                    @else
                        Invitado
                    @endauth
                </span>
                <div class="user-avatar">
                    @auth
                        {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
                    @else
                        <i class="bi bi-person"></i>
                    @endauth
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person me-2 text-gray-400"></i>
                        Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-gear me-2 text-gray-400"></i>
                        Configuracion
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-list-ul me-2 text-gray-400"></i>
                        Registro de Actividad
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="bi bi-box-arrow-right me-2 text-gray-400"></i>
                        Cerrar Sesion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- Logout Modal --}}
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Cerrar Sesion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Selecciona "Cerrar Sesion" si estas listo para terminar tu sesion actual.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Cerrar Sesion
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
