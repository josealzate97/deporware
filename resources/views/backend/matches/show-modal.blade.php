@php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
@php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
@php($venueModel = $match->relationLoaded('venue') ? $match->getRelation('venue') : null)
@php($feedback = $match->relationLoaded('feedback') ? $match->getRelation('feedback') : null)
@php($teamRating = $match->relationLoaded('teamRating') ? $match->getRelation('teamRating') : null)
@php($coachRosters = $teamModel && $teamModel->relationLoaded('managerRosters') ? $teamModel->getRelation('managerRosters')->where('status', 1) : collect())
@php($primaryCoachRoster = $coachRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_PRIMARY_COACH))
@php($assistantCoachRoster = $coachRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_ASSISTANT_COACH))
@php($primaryCoach = $primaryCoachRoster && $primaryCoachRoster->relationLoaded('user') ? $primaryCoachRoster->getRelation('user') : null)
@php($assistantCoach = $assistantCoachRoster && $assistantCoachRoster->relationLoaded('user') ? $assistantCoachRoster->getRelation('user') : null)
@php($isScheduled = (int) $match->match_status === \App\Models\MatchModel::STATUS_SCHEDULED)
@php($hasMatchReport = !empty($match->match_file))

<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-futbol"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Partido</h3>
            <div class="text-muted small fw-bold">Consulta el resumen general y valoraciones del encuentro</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="match-tabs">
        <input type="radio" id="match-tab-info" name="match-tabs" checked>
        <label for="match-tab-info"><i class="fa-solid fa-circle-info match-tab-label-icon"></i>Info del partido</label>

        <input type="radio" id="match-tab-technical" name="match-tabs" {{ $isScheduled ? 'disabled' : '' }}>
        <label for="match-tab-technical" class="{{ $isScheduled ? 'is-disabled' : '' }}"><i class="fa-solid fa-clipboard-check match-tab-label-icon"></i>Valoración Técnica</label>

        <input type="radio" id="match-tab-aptitudinal" name="match-tabs" {{ $isScheduled ? 'disabled' : '' }}>
        <label for="match-tab-aptitudinal" class="{{ $isScheduled ? 'is-disabled' : '' }}"><i class="fa-solid fa-brain match-tab-label-icon"></i>Valoración Aptitudinal</label>

        <div class="match-tab-panels w-100 mt-3">
            <div class="match-tab-panel" data-panel="info">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-calendar-days match-info-label-icon"></i>Fecha y hora</div>
                        <div class="match-info-value">{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</div>
                        <div class="match-info-inline-badges mt-2">
                            <span class="match-info-pill {{ (int) $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED ? 'match-info-pill-success' : 'match-info-pill-muted' }}">
                                {{ $statusOptions[$match->match_status] ?? 'Sin estado' }}
                            </span>
                            <span class="match-info-pill match-info-pill-muted">{{ $match->match_round ?: 'Sin jornada' }}</span>
                        </div>
                    </div>

                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-shield-halved match-info-label-icon"></i>Equipo</div>
                        <div class="match-info-value">{{ $teamModel?->name ?? '-' }}</div>
                        <div class="match-info-inline-badges mt-2">
                            <span class="match-info-pill match-info-pill-coach">
                                Entrenador: {{ $primaryCoach?->name ?? $assistantCoach?->name ?? 'Sin asignar' }}
                            </span>
                        </div>
                    </div>

                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-user-group match-info-label-icon"></i>Rival</div>
                        <div class="match-info-value">{{ $rivalModel?->name ?? '-' }}</div>
                    </div>

                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-house match-info-label-icon"></i>Local / Visitante</div>
                        <div class="match-info-value">{{ $sideOptions[$match->side] ?? '-' }}</div>
                    </div>

                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-chart-line match-info-label-icon"></i>Resultado</div>
                        <div class="match-info-value">{{ $isScheduled ? 'Pendiente' : ($resultOptions[$match->match_result] ?? '-') }}</div>
                        <div class="match-info-inline-badges mt-2">
                            <span class="match-info-pill match-info-pill-score">Marcador: {{ $match->final_score ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="match-info-item match-info-item-venue">
                        <div class="match-info-label"><i class="fa-solid fa-location-dot match-info-label-icon"></i>Sede</div>
                        <div class="match-info-value">{{ $venueModel?->name ?? '-' }}</div>
                        <div class="match-info-sub">Locación: {{ $match->location ?: '-' }}</div>
                    </div>
                </div>

                <div class="match-info-item mt-3">
                    <div class="match-info-label"><i class="fa-solid fa-note-sticky match-info-label-icon"></i>Notas del partido</div>
                    <div class="match-info-sub">{{ $match->match_notes ?: 'Sin notas registradas.' }}</div>
                </div>

                <div class="match-info-item mt-3">
                    <div class="match-info-label"><i class="fa-solid fa-file-lines match-info-label-icon"></i>Ficha del partido</div>
                    @if($hasMatchReport)
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                            <a href="{{ route('matches.view.report', $match->id) }}" target="_blank" rel="noopener" class="btn btn-sm match-file-action-btn">
                                <i class="fa-solid fa-eye me-1"></i> Visualizar
                            </a>
                            <a href="{{ route('matches.download.report', $match->id) }}" class="btn btn-sm match-file-download-btn">
                                <i class="fa-solid fa-download me-1"></i> Descargar ficha
                            </a>
                        </div>
                    @else
                        <div class="match-info-sub">No hay ficha de partido cargada.</div>
                    @endif
                </div>
            </div>

            <div class="match-tab-panel" data-panel="technical">
                @if($isScheduled)
                    <div class="text-muted">Este partido está agendado. La valoración técnica se habilita al completarlo.</div>
                @elseif(!$feedback)
                    <div class="text-muted">Sin valoración técnica registrada.</div>
                @else
                    <div class="match-info-grid">
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-tie match-info-label-icon"></i>Entrenador principal</div>
                            <div class="match-info-value">{{ $primaryCoach?->name ?? 'Sin asignar' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-check match-info-label-icon"></i>Entrenador secundario</div>
                            <div class="match-info-value">{{ $assistantCoach?->name ?? 'Sin asignar' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-chess-board match-info-label-icon"></i>Formación</div>
                            <div class="match-info-value">{{ $feedback->match_formation ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-bolt match-info-label-icon"></i>Fortaleza ofensiva</div>
                            <div class="match-info-value">{{ $feedback->attackStrength?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-triangle-exclamation match-info-label-icon"></i>Debilidad ofensiva</div>
                            <div class="match-info-value">{{ $feedback->attackWeakness?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-shield match-info-label-icon"></i>Fortaleza defensiva</div>
                            <div class="match-info-value">{{ $feedback->defenseStrength?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-shield-halved match-info-label-icon"></i>Debilidad defensiva</div>
                            <div class="match-info-value">{{ $feedback->defenseWeakness?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="match-info-item mt-3">
                        <div class="match-info-label"><i class="fa-solid fa-file-pen match-info-label-icon"></i>Notas técnicas</div>
                        <div class="match-info-sub">{{ $feedback->notes ?: 'Sin notas técnicas.' }}</div>
                    </div>
                @endif
            </div>

            <div class="match-tab-panel" data-panel="aptitudinal">
                @if($isScheduled)
                    <div class="text-muted">Este partido está agendado. La valoración aptitudinal se habilita al completarlo.</div>
                @elseif(!$teamRating)
                    <div class="text-muted">Sin valoración aptitudinal registrada.</div>
                @else
                    <div class="match-info-grid">
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-tie match-info-label-icon"></i>Entrenador principal</div>
                            <div class="match-info-value">{{ $primaryCoach?->name ?? 'Sin asignar' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-check match-info-label-icon"></i>Entrenador secundario</div>
                            <div class="match-info-value">{{ $assistantCoach?->name ?? 'Sin asignar' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-gavel match-info-label-icon"></i>Árbitro</div>
                            <div class="match-info-value">{{ $teamRating->referee_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-gear match-info-label-icon"></i>Técnico</div>
                            <div class="match-info-value">{{ $teamRating->coach_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-people-group match-info-label-icon"></i>Compañeros</div>
                            <div class="match-info-value">{{ $teamRating->teammates_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-user-group match-info-label-icon"></i>Rivales</div>
                            <div class="match-info-value">{{ $teamRating->opponents_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label"><i class="fa-solid fa-users match-info-label-icon"></i>Grada</div>
                            <div class="match-info-value">{{ $teamRating->fans_rating ?? '-' }}/10</div>
                        </div>
                    </div>
                    <div class="match-info-item mt-3">
                        <div class="match-info-label"><i class="fa-solid fa-clipboard-list match-info-label-icon"></i>Notas aptitudinales</div>
                        <div class="match-info-sub">{{ $teamRating->notes ?: 'Sin notas aptitudinales.' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
