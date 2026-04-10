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
                <div class="nav-item dropdown">
                    <button class="tenant-switcher dropdown-toggle"
                            id="tenantSwitcherBtn" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Cambiar escuela activa">
                        <span class="tenant-switcher__icon {{ $activeTenant ? 'tenant-switcher__icon--active' : '' }}">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <span class="tenant-switcher__meta">
                            <span class="tenant-switcher__name">{{ $activeTenant ? $activeTenant->name : 'Vista global' }}</span>
                            <span class="tenant-switcher__sub">{{ $activeTenant ? 'Escuela activa' : 'Super Admin' }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down tenant-switcher__chevron"></i>
                    </button>
                    @php
                        $switchableTenants = \App\Models\Tenant::where('status', \App\Models\Tenant::ACTIVE)->orderBy('name')->get();
                    @endphp
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:240px">
                        <li><h6 class="dropdown-header">Cambiar escuela</h6></li>
                        @forelse($switchableTenants as $t)
                            <li>
                                <form method="POST" action="{{ route('root.tenant.switch') }}">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center gap-2 {{ $activeTenant?->id === $t->id ? 'tenant-dropdown-item--active' : '' }}">
                                        @if($activeTenant?->id === $t->id)
                                            <i class="fa-solid fa-circle-check small" style="color:#7c3aed"></i>
                                        @else
                                            <i class="fa-solid fa-circle-dot small text-muted"></i>
                                        @endif
                                        {{ $t->name }}
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li>
                                <div class="px-3 py-3 text-center tenant-empty-state" style="background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);border-radius:8px;margin:0.25rem 0.5rem;">
                                    <span class="tenant-empty-icon d-inline-flex align-items-center justify-content-center mb-2"
                                          style="width:38px;height:38px;border-radius:50%;background:#ede9fe;border:1.5px solid #c4b5fd;">
                                        <i class="fa-solid fa-building" style="color:#7c3aed;font-size:1rem;"></i>
                                    </span>
                                    <div class="tenant-empty-title fw-semibold mb-1" style="color:#5b21b6;font-size:0.82rem;">Sin escuelas activas</div>
                                    <div class="tenant-empty-sub mb-2" style="color:#7c3aed;font-size:0.75rem;opacity:0.8;">Crea la primera para comenzar</div>
                                    <a href="{{ route('tenants.new') }}" class="tenant-empty-btn btn btn-sm w-100"
                                       style="background:#7c3aed;color:#fff;font-size:0.8rem;border:none;border-radius:8px;font-weight:700;">
                                        <i class="fa-solid fa-plus me-1"></i> Crear escuela
                                    </a>
                                </div>
                            </li>
                        @endforelse
                        @if($activeTenant)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('root.tenant.exit') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-right-from-bracket small"></i> Salir a lista global
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
                        <a class="dropdown-item text-dark" href="{{ route('profile') }}">
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
