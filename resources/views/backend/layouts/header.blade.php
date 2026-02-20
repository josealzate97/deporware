<nav class="app-header">

    <div class="header-left">
        <button class="btn header-icon" id="sidebar-toggle" aria-label="Alternar sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <div class="header-breadcrumb">
            @stack('breadcrumb')
        </div>
    </div>

    <div class="header-right">
        <div class="theme-toggle theme-toggle-lg theme-toggle-spaced">
            <i class="fas fa-sun"></i>
            <label class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="theme-switch" aria-label="Alternar modo">
            </label>
            <i class="fas fa-moon"></i>
        </div>

        @if(Auth::check())
            @php
                $firstName = trim((string) (Auth::user()->name ?? ''));
                $lastName = trim((string) (Auth::user()->lastname ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);
                $fallbackName = trim((string) (Auth::user()->username ?? ''));
                $displayName = $fullName !== '' ? $fullName : $fallbackName;
                $initials = '';
                if ($firstName !== '') {
                    $initials .= mb_substr($firstName, 0, 1);
                }
                if ($lastName !== '') {
                    $initials .= mb_substr($lastName, 0, 1);
                }
                if ($initials === '') {
                    $initials = mb_substr($fallbackName, 0, 2);
                }
                $initials = strtoupper($initials);
            @endphp

            <div class="nav-item dropdown">
                <a class="user-summary dropdown-toggle" href="#" id="userSummaryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menú de usuario">
                    <span class="user-initials">{{ $initials }}</span>
                    <span class="user-meta">
                        <span class="user-name">{{ $displayName }}</span>
                        <span class="user-role">
                            {{
                                Auth::user()->rol == 1 ? 'Soporte' :
                                (Auth::user()->rol == 2 ? 'Administrador' : 'Cajero')
                            }}
                        </span>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm user-menu">
                    <li class="text-center">
                        <p class="fw-bold small badge bg-secondary mb-2">
                            {{
                                Auth::user()->rol == 1 ? 'Soporte' :
                                (Auth::user()->rol == 2 ? 'Administrador' : 'Cajero')
                            }}
                        </p>
                    </li>

                    <li>
                        <a class="dropdown-item text-dark" href="{{ route('users.info', ['id' => Auth::user()->id]) }}">
                            <i class="fa-solid fa-user color-primary"></i>&nbsp;&nbsp;
                            Mi Perfil
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            <i class="fa-solid fa-sign-out-alt"></i>&nbsp;&nbsp;
                            Cerrar sesión
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none hover-danger">
                            @csrf
                        </form>

                    </li>
                </ul>
            </div>
        @endif
    </div>

</nav>
