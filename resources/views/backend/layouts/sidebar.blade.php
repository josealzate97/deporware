
<div class="sidebar">

    <a href="{{ route('home') }}" class="sidebar-brand d-flex">
        <span class="sidebar-title">DEPORWARE</span>
    </a>

    <ul class="sidebar-nav">

        <li class="sidebar-item">
            <a href="{{ route('home') }}" class="sidebar-link" url="home">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-dashboard"></i>
                </span>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="{{ route('staff.index') }}" class="sidebar-link" url="staff">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span>Personal</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="{{ route('players.index') }}" class="sidebar-link" url="players">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-people-group"></i>
                </span>
                <span>Jugadores</span>
            </a>
        </li>
        
        <li class="sidebar-item">
            <a href="{{ route('categories.index') }}" class="sidebar-link" url="categories">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                <span>Categorias</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="{{ route('teams.index') }}" class="sidebar-link" url="teams">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-shield"></i>
                </span>
                <span>Plantillas</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="{{ route('matches.index') }}" class="sidebar-link" url="matches">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-futbol"></i>
                </span>
                <span>Partidos</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="{{ route('trainings.index') }}" class="sidebar-link" url="trainings">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-dumbbell"></i>
                </span>
                <span>Entrenamientos</span>
            </a>
        </li>

    </ul>

    <ul class="sidebar-nav sidebar-bottom">

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
