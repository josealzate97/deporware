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
                $isRoot        = (int) Auth::user()->role === \App\Models\User::ROLE_ROOT;
                $activeTenant  = app()->bound('current_tenant') ? app('current_tenant') : null;
            @endphp

            {{-- Selector de escuela para ROOT --}}
            @if($isRoot)
                <div class="nav-item dropdown me-2">
                    <button class="btn btn-sm d-flex align-items-center gap-2 {{ $activeTenant ? 'btn-primary' : 'btn-outline-secondary' }}"
                            id="tenantSwitcherBtn" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Cambiar escuela activa">
                        <i class="fa-solid fa-building"></i>
                        <span class="d-none d-md-inline">
                            {{ $activeTenant ? $activeTenant->name : 'Vista global' }}
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:220px">
                        <li><h6 class="dropdown-header">Cambiar escuela</h6></li>
                        @foreach(\App\Models\Tenant::where('status', \App\Models\Tenant::ACTIVE)->orderBy('name')->get() as $t)
                            <li>
                                <form method="POST" action="{{ route('root.tenant.switch') }}">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center gap-2 {{ $activeTenant?->id === $t->id ? 'fw-bold text-primary' : '' }}">
                                        <i class="fa-solid fa-circle-dot small {{ $activeTenant?->id === $t->id ? 'text-primary' : 'text-muted' }}"></i>
                                        {{ $t->name }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                        @if($activeTenant)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('root.tenant.exit') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-muted d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-globe small"></i> Salir a vista global
                                    </button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

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

                $roleLabels = [
                    \App\Models\User::ROLE_ROOT => 'Super Admin',
                    \App\Models\User::ROLE_SPORT_MANAGER => 'Gerente Deportivo',
                    \App\Models\User::ROLE_COACH => 'Entrenador',
                    \App\Models\User::ROLE_COORDINATOR => 'Coordinador'
                ];

                $roleName = $roleLabels[Auth::user()->role ?? null] ?? 'Sin rol';
            @endphp

            <div class="nav-item dropdown">
                <a class="user-summary dropdown-toggle" href="#" id="userSummaryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menú de usuario">
                    <span class="user-initials">{{ $initials }}</span>
                    <span class="user-meta">
                        <span class="user-name">{{ $displayName }}</span>
                        <span class="user-role">
                            {{ $roleName }}
                        </span>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm user-menu">
                    <li class="user-role-badge-wrap">
                        <p class="fw-bold small badge user-role-badge mb-2">
                            {{ $roleName }}
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
