@extends('backend.layouts.main')

@php($configuration = $configuration ?? null)
@php($tenant = $tenant ?? null)
@php($summaryCards = $summaryCards ?? [])
@php($upcomingAgenda = $upcomingAgenda ?? collect())
@php($recentMatches = $recentMatches ?? collect())
@php($recentTrainings = $recentTrainings ?? collect())
@php($teamRosterLoad = $teamRosterLoad ?? collect())
@php($monthlyActivity = $monthlyActivity ?? collect())

@section('title', 'Inicio')

@push('styles')
    @vite(['resources/css/modules/dashboard.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/dashboard.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb')
        @endpush

        <div class="card p-4 section-hero dashboard-hero surface-gradient-day">
            <div class="d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between gap-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="section-hero-icon dashboard-hero-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>

                    <div class="flex-grow-1">
                        <div class="dashboard-eyebrow">Panel general</div>
                        <h2 class="fw-bold mb-1">
                            {{ $configuration?->name ?: 'Bienvenido a Deporware' }}
                        </h2>
                        <div class="text-muted small fw-bold">
                            {{ $configuration?->sport_label ? 'Vista rápida de ' . $configuration->sport_label . ' con la operación del club en un solo lugar' : 'Todo tu ecosistema deportivo, organizado y bajo control' }}
                        </div>
                        @if($tenant)
                        <div class="mt-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold small">
                                <i class="fa-solid fa-building me-1"></i>{{ $tenant->name }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="dashboard-hero-side">
                    <div class="dashboard-hero-badge">
                        <span class="dashboard-hero-badge__label">Usuarios activos</span>
                        <strong>{{ $activeUsers }}</strong>
                    </div>
                    <div class="dashboard-hero-badge">
                        <span class="dashboard-hero-badge__label">Hoy</span>
                        <strong>{{ now()->locale('es')->translatedFormat('d M Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-summary-grid mt-4">
            @foreach($summaryCards as $card)
                <div class="dashboard-summary-card dashboard-summary-card--{{ $card['tone'] ?? 'primary' }}">
                    <div class="dashboard-summary-card__icon">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <div class="dashboard-summary-card__body">
                        <div class="dashboard-summary-card__label">{{ $card['label'] }}</div>
                        <div class="dashboard-summary-card__value" data-dashboard-counter="{{ $card['value'] }}">{{ $card['value'] }}</div>
                        <div class="dashboard-summary-card__meta">{{ $card['meta'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="dashboard-layout mt-4">
            <div class="dashboard-main-column">
                <div class="dashboard-panel">
                    <div class="dashboard-panel__header">
                        <div>
                            <div class="dashboard-panel__eyebrow">Actividad</div>
                            <h3 class="dashboard-panel__title">Partidos y entrenamientos por mes</h3>
                            <p class="dashboard-panel__subtitle">Una lectura rápida de la carga competitiva y de entrenamiento de los últimos meses.</p>
                        </div>
                    </div>

                    <div class="dashboard-activity" data-dashboard-activity='@json($monthlyActivity)'>
                        <div class="dashboard-activity__chart" data-dashboard-activity-chart></div>
                        <div class="dashboard-activity__legend">
                            <span><i class="fa-solid fa-futbol"></i> Partidos</span>
                            <span><i class="fa-solid fa-dumbbell"></i> Entrenamientos</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-dual-grid mt-4">
                    <div class="dashboard-panel">
                        <div class="dashboard-panel__header">
                            <div>
                                <div class="dashboard-panel__eyebrow">Reciente</div>
                                <h3 class="dashboard-panel__title">Últimos partidos</h3>
                            </div>
                            <a href="{{ route('matches.index') }}" class="dashboard-panel__link dashboard-panel__link--purple">Ver todos</a>
                        </div>

                        <div class="dashboard-list">
                            @forelse($recentMatches as $match)
                                @php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
                                @php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
                                <a href="{{ route('matches.index', ['view' => 'calendar', 'month' => $match->match_date?->format('Y-m')]) }}" class="dashboard-list-item">
                                    <div class="dashboard-list-item__icon dashboard-list-item__icon--match">
                                        <i class="fa-solid fa-futbol"></i>
                                    </div>
                                    <div class="dashboard-list-item__body">
                                        <div class="dashboard-list-item__title">{{ $teamModel?->name ?? 'Equipo' }} vs {{ $rivalModel?->name ?? 'Rival' }}</div>
                                        <div class="dashboard-list-item__meta">
                                            <span>{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</span>
                                            <span>{{ \App\Models\MatchModel::statusOptions()[$match->match_status] ?? 'Sin estado' }}</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-list-item__pill">{{ $match->final_score ?: 'Pendiente' }}</div>
                                </a>
                            @empty
                                <div class="dashboard-empty-state">Aún no hay partidos registrados.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dashboard-panel">
                        <div class="dashboard-panel__header">
                            <div>
                                <div class="dashboard-panel__eyebrow">Reciente</div>
                                <h3 class="dashboard-panel__title">Últimos entrenamientos</h3>
                            </div>
                            <a href="{{ route('trainings.index') }}" class="dashboard-panel__link dashboard-panel__link--purple">Ver todos</a>
                        </div>

                        <div class="dashboard-list">
                            @forelse($recentTrainings as $training)
                                @php($teamModel = $training->relationLoaded('team') ? $training->getRelation('team') : null)
                                <a href="{{ route('trainings.index', ['view' => 'calendar', 'month' => $training->created_at?->format('Y-m')]) }}" class="dashboard-list-item">
                                    <div class="dashboard-list-item__icon dashboard-list-item__icon--training">
                                        <i class="fa-solid fa-dumbbell"></i>
                                    </div>
                                    <div class="dashboard-list-item__body">
                                        <div class="dashboard-list-item__title">{{ $training->name ?: 'Entrenamiento' }}</div>
                                        <div class="dashboard-list-item__meta">
                                            <span>{{ $teamModel?->name ?? 'Sin equipo' }}</span>
                                            <span>{{ $training->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-list-item__pill">{{ $training->duration ? $training->duration . ' min' : '-' }}</div>
                                </a>
                            @empty
                                <div class="dashboard-empty-state">Aún no hay entrenamientos registrados.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-side-column">
                <div class="dashboard-panel surface-gradient-day">
                    <div class="dashboard-panel__header">
                        <div>
                            <div class="dashboard-panel__eyebrow">Agenda</div>
                            <h3 class="dashboard-panel__title">Próximos eventos</h3>
                        </div>
                    </div>

                    <div class="dashboard-stack">
                        @forelse($upcomingAgenda as $item)
                            <a href="{{ $item['route'] }}" class="dashboard-agenda-item dashboard-agenda-item--{{ $item['type'] }}">
                                <div class="dashboard-agenda-item__icon">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="dashboard-agenda-item__body">
                                    <div class="dashboard-agenda-item__title">{{ $item['title'] }}</div>
                                    <div class="dashboard-agenda-item__subtitle">{{ $item['subtitle'] }}</div>
                                    <div class="dashboard-agenda-item__meta">{{ $item['datetime']?->locale('es')->translatedFormat('D, d M · H:i') ?? '-' }}</div>
                                </div>
                                <div class="dashboard-agenda-item__badge">{{ $item['badge'] }}</div>
                            </a>
                        @empty
                            <div class="dashboard-empty-state">No hay eventos próximos en agenda.</div>
                        @endforelse
                    </div>
                </div>

                <div class="dashboard-panel mt-4">
                    <div class="dashboard-panel__header">
                        <div>
                            <div class="dashboard-panel__eyebrow">Plantillas</div>
                            <h3 class="dashboard-panel__title">Carga por equipo</h3>
                        </div>
                    </div>

                    <div class="dashboard-stack">
                        @forelse($teamRosterLoad as $teamLoad)
                            <div class="dashboard-team-load">
                                <div class="dashboard-team-load__top">
                                    <div>
                                        <div class="dashboard-team-load__name">{{ $teamLoad['name'] }}</div>
                                        <div class="dashboard-team-load__meta">{{ $teamLoad['season'] ?: 'Sin temporada' }}</div>
                                    </div>
                                    <div class="dashboard-team-load__count">{{ $teamLoad['player_count'] }}</div>
                                </div>
                                <div class="dashboard-team-load__bar">
                                    <span style="width: {{ min(($teamLoad['player_count'] / max(($teamRosterLoad->max('player_count') ?: 1), 1)) * 100, 100) }}%"></span>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty-state">No hay equipos con plantillas activas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
