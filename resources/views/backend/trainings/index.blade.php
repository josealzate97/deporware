@extends('backend.layouts.main')

@section('title', 'Entrenamientos')

@push('styles')
    @vite(['resources/css/modules/trainings.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/trainings.js'])
@endpush

@section('content')
    @php($isCoordinator = $isCoordinator ?? ((int) auth()->user()?->role === \App\Models\User::ROLE_COORDINATOR))

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'trainings.index',
                    'icon' => 'fas fa-dumbbell',
                    'label' => 'Entrenamientos'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Entrenamientos</h2>
                        <div class="text-muted small fw-bold">Consulta y administra las sesiones de entrenamiento programadas y realizadas</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('trainings.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Entrenamiento
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">
            <div class="card p-0 mt-4 section-card">
                @php($trainingsTotal = $trainings->count())
                @php($trainingsBaseQuery = request()->except('page'))
                <div class="players-toolbar">
                    <div class="section-results-meta trainings-results-meta">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold">Resultados</span>
                            <span class="text-muted">
                                Mostrando {{ $trainingsTotal > 0 ? 1 : 0 }}-{{ $trainingsTotal }} de {{ $trainingsTotal }}
                            </span>
                        </div>
                        <div class="matches-view-switch" role="tablist" aria-label="Cambiar vista de entrenamientos">
                            <a href="{{ route('trainings.index', array_merge($trainingsBaseQuery, ['view' => 'list'])) }}" class="matches-view-tab {{ ($activeView ?? 'list') === 'list' ? 'is-active' : '' }}" role="tab" aria-selected="{{ ($activeView ?? 'list') === 'list' ? 'true' : 'false' }}">
                                <i class="fa-solid fa-table-list me-1"></i> Listado
                            </a>
                            <a href="{{ route('trainings.index', array_merge($trainingsBaseQuery, ['view' => 'calendar'])) }}" class="matches-view-tab {{ ($activeView ?? 'list') === 'calendar' ? 'is-active' : '' }}" role="tab" aria-selected="{{ ($activeView ?? 'list') === 'calendar' ? 'true' : 'false' }}">
                                <i class="fa-regular fa-calendar me-1"></i> Calendario
                            </a>
                        </div>
                    </div>

                    <form class="section-toolbar players-filters matches-filters" method="GET" action="{{ route('trainings.index') }}">
                        <input type="hidden" name="view" value="{{ $activeView ?? 'list' }}">
                        @if(($activeView ?? 'list') === 'calendar')
                            <input type="hidden" name="month" value="{{ $calendarMonth ?? now()->format('Y-m') }}">
                        @endif

                        <div class="section-search">
                            <i class="fas fa-search"></i>
                            <label class="visually-hidden" for="trainingsSearch">Buscar entrenamiento</label>
                            <input
                                type="search"
                                id="trainingsSearch"
                                name="search"
                                class="form-control form-control-sm"
                                placeholder="Buscar por entrenamiento, equipo o sede..."
                                value="{{ $search ?? '' }}"
                            >
                        </div>

                        <label class="visually-hidden" for="trainingsStatus">Estado</label>
                        <select class="form-select form-select-sm section-filter" id="trainingsStatus" name="status" onchange="this.form.requestSubmit()">
                            <option value="">Todos</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" {{ (string) ($selectedStatus ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <label class="visually-hidden" for="trainingsTeam">Equipo</label>
                        <select class="form-select form-select-sm section-filter" id="trainingsTeam" name="team" onchange="this.form.requestSubmit()">
                            <option value="">Todos los equipos</option>
                            @foreach($teamOptions as $teamId => $teamName)
                                <option value="{{ $teamId }}" {{ (string) ($selectedTeam ?? '') === (string) $teamId ? 'selected' : '' }}>
                                    {{ $teamName }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm section-filter-btn">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('trainings.index') }}" class="btn btn-sm section-clear-btn">
                            <i class="fas fa-rotate-left"></i> Limpiar
                        </a>
                    </form>
                </div>

                @if(($activeView ?? 'list') === 'calendar')
                    @include('backend.trainings.calendar', ['isCoordinator' => $isCoordinator])
                @else
                    @include('backend.trainings.table', ['isCoordinator' => $isCoordinator])
                @endif
            </div>

            <div class="info-overlay" x-show="open" x-transition.opacity x-cloak @click.self="closeModal">
                <div class="info-panel" :class="open ? 'is-open' : ''" x-show="open" x-transition>
                    <div class="info-header">
                        <span x-text="title"></span>
                        <button type="button" class="info-close" @click="closeModal">&times;</button>
                    </div>
                    <div class="info-body" x-html="content"></div>
                </div>
            </div>

        </div>

@endsection
