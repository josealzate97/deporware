@extends('backend.layouts.main')

@section('title', 'Partidos')

@push('styles')
    @vite(['resources/css/modules/matches.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/matches.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'matches.index',
                    'icon' => 'fa-solid fa-futbol',
                    'label' => 'Partidos'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-futbol"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Partidos</h2>
                        <div class="text-muted small fw-bold">Administra los encuentros programados, resultados y estado de cada partido</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('matches.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Partido
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">

            <div class="card p-0 mt-4 section-card">

                <div class="players-toolbar">

                    <div class="players-toolbar-meta p-3">

                        <span class="fw-bold">Resultados</span>
                    <span class="match-results-meta">
                        @if($matches->total() > 0)
                            <span class="match-results-badge">Mostrando {{ $matches->firstItem() }}-{{ $matches->lastItem() }} de {{ $matches->total() }}</span>
                        @else
                            <span class="match-results-badge">Sin resultados</span>
                        @endif
                    </span>
                    </div>

                    <form class="section-toolbar players-filters matches-filters" method="GET" action="{{ route('matches.index') }}">
                        
                        <div class="section-search">
                            <i class="fas fa-search"></i>
                            <label class="visually-hidden" for="matchesSearch">Buscar partido</label>
                                <input
                                type="search"
                                id="matchesSearch"
                                name="search"
                                class="form-control form-control-sm"
                                placeholder="Buscar por fecha, equipo o rival..."
                                value="{{ $search ?? '' }}">
                        </div>

                        <label class="visually-hidden" for="matchesStatus">Estado</label>
                        <select class="form-select form-select-sm section-filter" id="matchesStatus" name="status" onchange="this.form.requestSubmit()">
                            <option value="">Todos</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" {{ (string) ($selectedStatus ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="autocomplete section-filter" data-autocomplete='@json(collect($teamOptions)->map(fn($name, $id) => ["id" => $id, "name" => $name])->values())'>
                            <label class="visually-hidden" for="matchesTeamInput">Equipo</label>
                            <i class="fas fa-search"></i>
                            <input
                                type="search"
                                id="matchesTeamInput"
                                class="form-control form-control-sm"
                                data-autocomplete-input
                                placeholder="Equipo..."
                                autocomplete="off"
                                value="{{ $selectedTeamName ?? '' }}"
                            >
                            <input type="hidden" name="team" data-autocomplete-hidden value="{{ $selectedTeam ?? '' }}">
                            <div class="autocomplete-list" data-autocomplete-list></div>
                        </div>

                        <div class="autocomplete section-filter" data-autocomplete='@json(collect($rivalOptions)->map(fn($name, $id) => ["id" => $id, "name" => $name])->values())'>
                            <label class="visually-hidden" for="matchesRivalInput">Rival</label>
                            <i class="fas fa-search"></i>
                            <input
                                type="search"
                                id="matchesRivalInput"
                                class="form-control form-control-sm"
                                data-autocomplete-input
                                placeholder="Rival..."
                                autocomplete="off"
                                value="{{ $selectedRivalName ?? '' }}"
                            >
                            <input type="hidden" name="rival" data-autocomplete-hidden value="{{ $selectedRival ?? '' }}">
                            <div class="autocomplete-list" data-autocomplete-list></div>
                        </div>

                        <button type="submit" class="btn btn-sm section-filter-btn">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('matches.index') }}" class="btn btn-sm section-clear-btn">
                            <i class="fas fa-rotate-left"></i> Limpiar
                        </a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle section-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Partido</th>
                                <th>Detalle</th>
                                <th>Resultado</th>
                                <th>Estado</th>
                                <th>Valoraciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matches as $match)
                                <tr data-id="{{ $match->id }}">
                                    <td>
                                        <div class="fw-semibold">{{ $match->match_date?->format('Y-m-d') ?? '-' }}</div>
                                        <div class="text-muted small">{{ $match->match_date?->format('H:i') ?? '-' }}</div>
                                    </td>
                                    @php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
                                    @php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
                                    @php($feedbackModel = $match->relationLoaded('feedback') ? $match->getRelation('feedback') : null)
                                    @php($ratingModel = $match->relationLoaded('teamRating') ? $match->getRelation('teamRating') : null)
                                    <td>
                                        <div class="fw-semibold">{{ $teamModel?->name ?? ($match->team ? 'Sin equipo vinculado' : '-') }}</div>
                                        <div class="text-muted small">vs {{ $rivalModel?->name ?? ($match->rival ? 'Sin rival vinculado' : '-') }}</div>
                                    </td>
                                    <td>
                                        <div><span class="meta-badge">{{ $sideOptions[$match->side] ?? '-' }}</span></div>
                                        <div class="text-muted small mt-1">Jornada: {{ $match->match_round ?: '-' }}</div>
                                    </td>
                                    <td>
                                        @if($match->match_status === \App\Models\MatchModel::STATUS_SCHEDULED)
                                            <span class="match-result-pill match-result-pill-pending">Pendiente</span>
                                        @else
                                            @php($resultLabel = $resultOptions[$match->match_result] ?? '-')
                                            @php($resultPillClass = match ((int) $match->match_result) {
                                                \App\Models\MatchModel::RESULT_WIN => 'match-result-pill-win',
                                                \App\Models\MatchModel::RESULT_DRAW => 'match-result-pill-draw',
                                                \App\Models\MatchModel::RESULT_LOSS => 'match-result-pill-loss',
                                                default => 'match-result-pill-draw',
                                            })
                                            <div>
                                                <span class="match-result-pill {{ $resultPillClass }}">{{ $resultLabel }}</span>
                                            </div>
                                            <div class="text-muted small mt-1">Marcador: <span class="fw-bold">{{ $match->final_score ?: '-' }}</span></div>
                                        @endif
                                    </td>
                                    <td>
                                        @php($statusLabel = $statusOptions[$match->match_status] ?? 'Sin estado')
                                        <span class="status-pill {{ $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED ? 'status-pill-success' : 'status-pill-muted' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($feedbackModel)
                                                <span class="meta-badge">Técnica</span>
                                            @else
                                                <span class="meta-badge text-muted">Sin técnica</span>
                                            @endif

                                            @if($ratingModel)
                                                <span class="meta-badge">Aptitudinal</span>
                                            @else
                                                <span class="meta-badge text-muted">Sin aptitudinal</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-icon text-primary"
                                            @click="openModal('{{ route('matches.show', $match->id) }}?modal=1')"
                                            aria-label="Ver información del partido" title="Ver información">
                                            <i class="fas fa-circle-info"></i>
                                        </button>
                                        <a href="{{ route('matches.edit', $match->id) }}" class="btn btn-icon btn-icon-edit"
                                            aria-label="Editar partido" title="Editar partido">
                                            <i class="fas fa-edit mt-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay partidos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('backend.components.pagination', [
                    'paginator' => $matches,
                    'ariaLabel' => 'Paginador de partidos',
                ])
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

    </div>

@endsection
