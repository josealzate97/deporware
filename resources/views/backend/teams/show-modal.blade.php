@php($typeLabel = ($team->type ?? 0) === \App\Models\Team::TYPE_FORMATIVE ? 'Formativo' : 'Competitivo')
@php($coachCount = ($primaryCoach ? 1 : 0) + ($assistantCoach ? 1 : 0))
@php($positionOptions = \App\Models\Player::positionOptions())
@php($activePlayers = $team->playerRosters->where('status', 1)->filter(function ($roster) { return $roster->getRelation('player'); })->values())
@php($latestMatches = $team->matches->take(10))
@php($latestTrainings = $team->trainings->take(10))

<div class="show-modal-mint">
    <div class="section-hero mb-3">
        <div class="d-flex align-items-start gap-3">
            <div class="section-hero-icon">
                <i class="fa-solid fa-shield"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Información de la Plantilla</h3>
                <div class="text-muted small fw-bold">Consulta el detalle general de la plantilla</div>
            </div>
        </div>
    </div>

    <div class="team-modal-tabs">
        <input type="radio" id="team-tab-info" name="team-tab" checked>
        <label class="team-modal-tab" for="team-tab-info">Información general</label>

        <input type="radio" id="team-tab-players" name="team-tab">
        <label class="team-modal-tab" for="team-tab-players">Jugadores ({{ $activePlayers->count() }})</label>

        <input type="radio" id="team-tab-matches" name="team-tab">
        <label class="team-modal-tab" for="team-tab-matches">Partidos ({{ $latestMatches->count() }})</label>

        <input type="radio" id="team-tab-trainings" name="team-tab">
        <label class="team-modal-tab" for="team-tab-trainings">Entrenamientos ({{ $latestTrainings->count() }})</label>

        <div class="team-modal-panels">
            <div class="team-modal-panel" data-panel="info">
                <div class="card p-3 section-card">
                    <div class="entity-info-grid">
                        <div class="entity-info-item">
                            <div class="entity-info-label">
                                <i class="fa-solid fa-shield text-primary me-2"></i>
                                Nombre
                            </div>
                            <div class="entity-info-value">{{ $team->name }}</div>
                            <div class="entity-info-sub">
                                <span class="meta-badge">{{ $typeLabel }}</span>
                            </div>
                        </div>
                        <div class="entity-info-item">
                            <div class="entity-info-label">
                                <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                                Temporada
                            </div>
                            <div class="entity-info-value">{{ $team->season }}</div>
                        </div>
                        <div class="entity-info-item">
                            <div class="entity-info-label">
                                <i class="fa-solid fa-calendar-check text-primary me-2"></i>
                                Año
                            </div>
                            <div class="entity-info-value">{{ $team->year }}</div>
                            <div class="entity-info-sub">
                                @if($team->status)
                                    <span class="status-pill status-pill-success">Activa</span>
                                @else
                                    <span class="status-pill status-pill-muted">Inactiva</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 section-card mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold">Sedes asociadas</div>
                        <span class="meta-badge">{{ $team->venues->count() }} sede{{ $team->venues->count() === 1 ? '' : 's' }}</span>
                    </div>
                    @if($team->venues->isEmpty())
                        <div class="empty-state-soft"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>Sin sedes asociadas.</div>
                    @else
                        <div class="row g-2 mt-2">
                            @foreach($team->venues as $venue)
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="team-info-item h-100">
                                        <span class="team-avatar-badge">
                                            <i class="fa-solid fa-building"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $venue->name }}</div>
                                            <span class="meta-badge">{{ $venue->city }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card p-3 section-card mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold">Entrenadores</div>
                        <span class="meta-badge">{{ $coachCount }} entrenador{{ $coachCount === 1 ? '' : 'es' }}</span>
                    </div>
                    @if(!$primaryCoach && !$assistantCoach)
                        <div class="empty-state-soft"><i class="fa-solid fa-user-slash" aria-hidden="true"></i>Sin entrenadores asignados.</div>
                    @else
                        <div class="row g-2 mt-2">
                            @if($primaryCoach)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="team-info-item team-coach-card h-100">
                                        <span class="team-avatar-badge">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $primaryCoach->name }}</div>
                                            <span class="meta-badge">Entrenador principal</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($assistantCoach)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="team-info-item team-coach-card h-100">
                                        <span class="team-avatar-badge">
                                            <i class="fa-solid fa-user"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $assistantCoach->name }}</div>
                                            <span class="meta-badge">Entrenador asistente</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="team-modal-panel" data-panel="players">
                <div class="card p-3 section-card">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="fw-semibold">Jugadores seleccionados</div>
                    <span class="meta-badge">{{ $activePlayers->count() }} jugador{{ $activePlayers->count() === 1 ? '' : 'es' }}</span>
                </div>

                @if($activePlayers->isEmpty())
                    <div class="empty-state-soft"><i class="fa-solid fa-users-slash" aria-hidden="true"></i>No hay jugadores asignados.</div>
                @else
                    <div class="row g-2">
                        @foreach($activePlayers as $roster)
                            @php($player = $roster->getRelation('player'))
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="team-player-card">
                                    <span class="team-player-number">{{ $roster->dorsal ?? '-' }}</span>
                                    <span class="team-player-main">
                                        <span class="team-player-meta">
                                            <span class="team-player-name">{{ $player->name }} {{ $player->lastname }}</span>
                                            <span class="meta-badge team-player-position-badge">{{ $positionOptions[$player->primary_position ?? $roster->position] ?? 'Sin posición' }}</span>
                                        </span>
                                        <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'player']) }}" class="btn btn-icon team-player-link" title="Ver más de {{ $player->name }} {{ $player->lastname }}" aria-label="Ver más de {{ $player->name }} {{ $player->lastname }}">
                                            <i class="fa-solid fa-circle-info"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            </div>
            <div class="team-modal-panel" data-panel="matches">
                <div class="card p-3 section-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold">Ultimos partidos del equipo</div>
                        <span class="meta-badge">{{ $latestMatches->count() }} partido{{ $latestMatches->count() === 1 ? '' : 's' }}</span>
                    </div>

                    @if($latestMatches->isEmpty())
                        <div class="empty-state-soft"><i class="fa-solid fa-futbol" aria-hidden="true"></i>Sin partidos registrados.</div>
                    @else
                        <div class="row g-2">
                            @foreach($latestMatches as $match)
                                @php($statusLabel = \App\Models\MatchModel::statusOptions()[$match->match_status] ?? 'Sin estado')
                                @php($isCompletedMatch = (int) $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED)
                                @php($resultLabel = \App\Models\MatchModel::resultOptions()[$match->match_result] ?? 'Sin resultado')
                                @php($resultClass = match ((int) $match->match_result) {
                                    \App\Models\MatchModel::RESULT_WIN => 'team-match-badge--result-win',
                                    \App\Models\MatchModel::RESULT_LOSS => 'team-match-badge--result-loss',
                                    \App\Models\MatchModel::RESULT_DRAW => 'team-match-badge--result-draw',
                                    default => 'team-match-badge--result-neutral',
                                })
                                <div class="col-12 col-lg-6">
                                    <div class="team-info-item team-match-card {{ $isCompletedMatch ? 'team-match-card--completed' : '' }} h-100">
                                        <span class="team-avatar-badge">
                                            <i class="fa-solid fa-futbol"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $team->name }} vs {{ $match->rival?->name ?? 'Sin rival' }}</div>
                                            <div class="text-muted small">{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</div>
                                            <div class="team-match-badges mt-1">
                                                <span class="team-match-badge team-match-badge--status">{{ $statusLabel }}</span>
                                                @if($isCompletedMatch)
                                                    <span class="team-match-badge {{ $resultClass }}">{{ $resultLabel }}</span>
                                                    <span class="team-match-badge team-match-badge--score">Marcador: {{ $match->final_score ?: '-' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="team-modal-panel" data-panel="trainings">
                <div class="card p-3 section-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold">Ultimos entrenamientos del equipo</div>
                        <span class="meta-badge">{{ $latestTrainings->count() }} entrenamiento{{ $latestTrainings->count() === 1 ? '' : 's' }}</span>
                    </div>

                    @if($latestTrainings->isEmpty())
                        <div class="empty-state-soft"><i class="fa-solid fa-dumbbell" aria-hidden="true"></i>Sin entrenamientos registrados.</div>
                    @else
                        <div class="row g-2">
                            @foreach($latestTrainings as $training)
                                <div class="col-12 col-lg-6">
                                    <div class="team-info-item h-100">
                                        <span class="team-avatar-badge">
                                            <i class="fa-solid fa-dumbbell"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $training->name ?: 'Entrenamiento' }}</div>
                                            <div class="text-muted small">{{ $training->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                                            <span class="meta-badge mt-1">{{ ((int) $training->status === \App\Models\Training::ACTIVE) ? 'Activo' : 'Inactivo' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
