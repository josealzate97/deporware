
<div class="sidebar">

    <a href="{{ route('home') }}" class="sidebar-brand d-flex">
        <span class="sidebar-title">
            <img src="{{ asset('images/branding/logo_half.png') }}" alt="Logo deporware" class="sidebar-logo">
        </span>
    </a>

    @php
        $sidebarActiveTenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        $sidebarIsRoot = auth()->check() && (int) auth()->user()->role === \App\Models\User::ROLE_ROOT;
    @endphp

    @if($sidebarIsRoot && $sidebarActiveTenant)
        <div class="sidebar-tenant-banner">
            <div class="sidebar-tenant-banner__top">
                <span class="sidebar-tenant-banner__icon"><i class="fa-solid fa-building"></i></span>
                <div class="sidebar-tenant-banner__info">
                    <div class="sidebar-tenant-banner__label">Administrando</div>
                    <div class="sidebar-tenant-banner__name" title="{{ $sidebarActiveTenant->name }}">{{ $sidebarActiveTenant->name }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('root.tenant.exit') }}" class="mt-2">
                @csrf
                <button type="submit" class="sidebar-tenant-banner__exit">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Salir a vista global
                </button>
            </form>
        </div>
    @endif

    <ul class="sidebar-nav">

        @foreach(auth()->user()->menuItems() as $item)
        <li class="sidebar-item">
            <a href="{{ route($item['route']) }}" class="sidebar-link" url="{{ $item['url'] }}">
                <span class="sidebar-icon">
                    <i class="fa-solid {{ $item['icon'] }}"></i>
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        </li>
        @endforeach

    </ul>

    <ul class="sidebar-nav sidebar-bottom">

        @if(!(isset($sidebarIsRoot) && $sidebarIsRoot && !$sidebarActiveTenant))
        <li class="sidebar-item">
            <a href="{{ route('configurations.index') }}" class="sidebar-link" url="configurations">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-cog"></i>
                </span>
                <span>Configuración</span>
            </a>
        </li>
        @endif

        <li class="sidebar-item">

            <a href="{{ route('logout') }}" class="sidebar-link text-danger"
                onclick="event.preventDefault();
                document.getElementById('logout-form-sidebar').submit();">
                <span class="sidebar-icon text-danger">
                    <i class="fa-solid fa-sign-out-alt"></i>
                </span>
                <span>Cerrar Sesión</span>
            </a>

            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>

        </li>

    </ul>

</div>
