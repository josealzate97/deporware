<div class="config-tabs mb-5">

    @can('config:edit-school')
    <a href="{{ route('configurations.index') }}" class="config-tab {{ ($activeTab ?? 'general') === 'general' ? 'is-active' : '' }}">
        <i class="fa-solid fa-sliders config-tab-icon"></i>
        Informacion General
    </a>
    @endcan

    <a href="{{ route('configurations.rivals.index') }}" class="config-tab {{ ($activeTab ?? '') === 'rivals' ? 'is-active' : '' }}">
        <i class="fa-solid fa-shield config-tab-icon"></i>
        Rivales
    </a>

    <a href="{{ route('configurations.points.index') }}" class="config-tab {{ ($activeTab ?? '') === 'points' ? 'is-active' : '' }}">
        <i class="fa-solid fa-list-check config-tab-icon"></i>
        Puntos debiles/fuertes
    </a>

</div>
