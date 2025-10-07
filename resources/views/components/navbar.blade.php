<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Logo/Brand -->
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-building me-2"></i>
            Sistema Remesas
        </a>

        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left side navigation -->
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('remesa.upload.form') ? 'active' : '' }}" href="{{ route('remesa.upload.form') }}">
                                <i class="bi bi-upload me-1"></i>Nueva Remesa
                            </a>
                        </li>
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('remesa.lista') ? 'active' : '' }}" href="{{ route('remesa.lista') }}">
                            <i class="bi bi-list-ul me-1"></i>Mis Remesas
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('remesas.general') ? 'active' : '' }}" href="{{ route('remesas.general') }}">
                            <i class="bi bi-grid-3x3-gap me-1"></i>Vista General
                        </a>
                    </li>
                    
                    @if(Auth::user()->isAdmin())
                        <!-- Dropdown para Gestión -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="gestionDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i>Gestión
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('usuarios.index') }}">
                                        <i class="bi bi-people me-2"></i>Gestión de Usuarios
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('quejas.index') }}">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Gestión de Quejas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('entregas.index') }}">
                                        <i class="bi bi-truck me-2"></i>Gestión de Entregas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('laboratorio.dashboard') }}">
                                        <i class="bi bi-clipboard2-data me-2"></i>Sistema de Laboratorio
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    
                    @if(Auth::user()->isUsuario() || Auth::user()->isOperarioCampo())
                        <!-- Quejas para usuarios normales y operarios -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quejas.*') ? 'active' : '' }}" href="{{ route('quejas.index') }}">
                                <i class="bi bi-exclamation-triangle me-1"></i>Quejas
                            </a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->isOperarioCampo())
                        <!-- Entregas para operarios de campo -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('entregas.*') ? 'active' : '' }}" href="{{ route('entregas.index') }}">
                                <i class="bi bi-truck me-1"></i>Mis Entregas
                            </a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->isTecnicoLaboratorio() || Auth::user()->isAdmin())
                        <!-- Sistema de Laboratorio -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('laboratorio.*') ? 'active' : '' }}" href="{{ route('laboratorio.dashboard') }}">
                                <i class="bi bi-clipboard2-data me-1"></i>Laboratorio
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <!-- Right side user menu -->
            <ul class="navbar-nav">
                @auth
                    <!-- User info -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <div class="d-none d-md-block">
                                    <div class="fw-semibold">{{ Auth::user()->nombre }}</div>
                                    <small class="text-light opacity-75">{{ Auth::user()->rol_texto }}</small>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-person-circle me-1"></i>
                                    {{ Auth::user()->correo }}
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            @if(Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.historial-passwords') }}">
                                        <i class="bi bi-key me-2"></i>Historial de Contraseñas
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- Logout form -->
@auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endauth

<style>
.navbar-brand {
    font-size: 1.25rem;
}

.nav-link.active {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 0.375rem;
}

.dropdown-menu {
    min-width: 200px;
}

.dropdown-header {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
    }
}
</style>
