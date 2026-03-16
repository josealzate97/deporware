@php($typeLabel = ($team->type ?? 0) === \App\Models\Team::TYPE_FORMATIVE ? 'Formativo' : 'Competitivo')
@php($coachCount = ($primaryCoach ? 1 : 0) + ($assistantCoach ? 1 : 0))
@php($positionOptions = \App\Models\Player::positionOptions())
@php($activePlayers = $team->playerRosters->where('status', 1)->filter(function ($roster) { return $roster->getRelation('player'); })->values())

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

                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold">Sedes asociadas</div>
                        <span class="meta-badge">{{ $team->venues->count() }} sede{{ $team->venues->count() === 1 ? '' : 's' }}</span>
                    </div>
                    @if($team->venues->isEmpty())
                        <div class="text-muted">Sin sedes asociadas.</div>
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

                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold">Entrenadores</div>
                        <span class="meta-badge">{{ $coachCount }} entrenador{{ $coachCount === 1 ? '' : 'es' }}</span>
                    </div>
                    @if(!$primaryCoach && !$assistantCoach)
                        <div class="text-muted">Sin entrenadores asignados.</div>
                    @else
                        <div class="row g-2 mt-2">
                            @if($primaryCoach)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="team-info-item h-100">
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
                                    <div class="team-info-item h-100">
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
        </div>

            <div class="team-modal-panel" data-panel="players">
                <div class="card p-3 section-card">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="fw-semibold">Jugadores seleccionados</div>
                    <span class="meta-badge">{{ $activePlayers->count() }} jugador{{ $activePlayers->count() === 1 ? '' : 'es' }}</span>
                </div>

                @if($activePlayers->isEmpty())
                    <div class="text-muted">No hay jugadores asignados.</div>
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
                                            <span class="team-player-position">{{ $positionOptions[$roster->position] ?? 'Sin posición' }}</span>
                                        </span>
                                        <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'player']) }}" class="btn btn-sm btn-outline-success team-player-link" title="Ver más de {{ $player->name }} {{ $player->lastname }}" aria-label="Ver más de {{ $player->name }} {{ $player->lastname }}">
                                            <i class="fa-solid fa-circle-info me-1"></i> Ver más
                                        </a>
                                    </span>
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
