@php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
@php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
@php($venueModel = $match->relationLoaded('venue') ? $match->getRelation('venue') : null)
@php($feedback = $match->relationLoaded('feedback') ? $match->getRelation('feedback') : null)
@php($teamRating = $match->relationLoaded('teamRating') ? $match->getRelation('teamRating') : null)
@php($isScheduled = (int) $match->match_status === \App\Models\MatchModel::STATUS_SCHEDULED)

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
        <label for="match-tab-info">Info del partido</label>

        <input type="radio" id="match-tab-technical" name="match-tabs" {{ $isScheduled ? 'disabled' : '' }}>
        <label for="match-tab-technical" class="{{ $isScheduled ? 'is-disabled' : '' }}">Valoración Técnica</label>

        <input type="radio" id="match-tab-aptitudinal" name="match-tabs" {{ $isScheduled ? 'disabled' : '' }}>
        <label for="match-tab-aptitudinal" class="{{ $isScheduled ? 'is-disabled' : '' }}">Valoración Aptitudinal</label>

        <div class="match-tab-panels w-100 mt-3">
            <div class="match-tab-panel" data-panel="info">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label">Fecha y hora</div>
                        <div class="match-info-value">{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Estado</div>
                        <div class="match-info-value">
                            <span class="status-pill {{ (int) $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED ? 'status-pill-success' : 'status-pill-muted' }}">
                                {{ $statusOptions[$match->match_status] ?? 'Sin estado' }}
                            </span>
                        </div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Equipo</div>
                        <div class="match-info-value">{{ $teamModel?->name ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Rival</div>
                        <div class="match-info-value">{{ $rivalModel?->name ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Local / Visitante</div>
                        <div class="match-info-value">{{ $sideOptions[$match->side] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Resultado</div>
                        <div class="match-info-value">{{ $isScheduled ? 'Pendiente' : ($resultOptions[$match->match_result] ?? '-') }}</div>
                        <div class="match-info-sub">Marcador: {{ $match->final_score ?: '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Jornada</div>
                        <div class="match-info-value">{{ $match->match_round ?: '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Sede</div>
                        <div class="match-info-value">{{ $venueModel?->name ?? '-' }}</div>
                        <div class="match-info-sub">Locación: {{ $match->location ?: '-' }}</div>
                    </div>
                </div>

                <div class="match-info-item mt-3">
                    <div class="match-info-label">Notas del partido</div>
                    <div class="match-info-sub">{{ $match->match_notes ?: 'Sin notas registradas.' }}</div>
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
                            <div class="match-info-label">Formación</div>
                            <div class="match-info-value">{{ $feedback->match_formation ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Fortaleza ofensiva</div>
                            <div class="match-info-value">{{ $feedback->attackStrength?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Debilidad ofensiva</div>
                            <div class="match-info-value">{{ $feedback->attackWeakness?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Fortaleza defensiva</div>
                            <div class="match-info-value">{{ $feedback->defenseStrength?->name ?? '-' }}</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Debilidad defensiva</div>
                            <div class="match-info-value">{{ $feedback->defenseWeakness?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="match-info-item mt-3">
                        <div class="match-info-label">Notas técnicas</div>
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
                            <div class="match-info-label">Árbitro</div>
                            <div class="match-info-value">{{ $teamRating->referee_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Técnico</div>
                            <div class="match-info-value">{{ $teamRating->coach_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Compañeros</div>
                            <div class="match-info-value">{{ $teamRating->teammates_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Rivales</div>
                            <div class="match-info-value">{{ $teamRating->opponents_rating ?? '-' }}/10</div>
                        </div>
                        <div class="match-info-item">
                            <div class="match-info-label">Grada</div>
                            <div class="match-info-value">{{ $teamRating->fans_rating ?? '-' }}/10</div>
                        </div>
                    </div>
                    <div class="match-info-item mt-3">
                        <div class="match-info-label">Notas aptitudinales</div>
                        <div class="match-info-sub">{{ $teamRating->notes ?: 'Sin notas aptitudinales.' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
