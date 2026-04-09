
<div class="sidebar">

    <a href="{{ route('home') }}" class="sidebar-brand d-flex">
        <span class="sidebar-title">
            <img src="{{ Vite::asset('resources/images/branding/logo_half.png') }}" alt="Logo deporware" class="sidebar-logo">
        </span>
    </a>

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

        <li class="sidebar-item">

            <a href="{{ route('configurations.index') }}" class="sidebar-link" url="configurations">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-cog"></i>
                </span>
                <span>Configuración</span>
            </a>

        </li>

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
