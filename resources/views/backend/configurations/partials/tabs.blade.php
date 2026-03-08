<div class="config-tabs mb-5">

    <a href="{{ route('configurations.index') }}" class="config-tab {{ ($activeTab ?? 'general') === 'general' ? 'is-active' : '' }}">
        Informacion General
    </a>

    <a href="{{ route('configurations.rivals.index') }}" class="config-tab {{ ($activeTab ?? '') === 'rivals' ? 'is-active' : '' }}">
        Rivales
    </a>

    <a href="{{ route('configurations.points.index') }}" class="config-tab {{ ($activeTab ?? '') === 'points' ? 'is-active' : '' }}">
        Puntos debiles/fuertes
    </a>

</div>
