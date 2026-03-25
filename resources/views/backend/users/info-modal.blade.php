@php
    $managerRoleLabels = [
        \App\Models\ManagerRoster::ROLE_PRIMARY_COACH => 'Entrenador principal',
        \App\Models\ManagerRoster::ROLE_ASSISTANT_COACH => 'Entrenador asistente',
    ];
@endphp

<div class="show-modal-mint">
    <div class="section-hero mb-3">
        <div class="d-flex align-items-start gap-3">
            <div class="section-hero-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Información del Usuario</h3>
                <div class="text-muted small fw-bold">Consulta los datos principales del personal</div>
            </div>
        </div>
    </div>

    <div class="team-modal-tabs user-modal-tabs">
        <input type="radio" id="user-tab-info" name="user-tab" checked>
        <label class="team-modal-tab" for="user-tab-info">Información general</label>

        @if($showVenueTab)
            <input type="radio" id="user-tab-venues" name="user-tab">
            <label class="team-modal-tab" for="user-tab-venues">Sedes ({{ $venues->count() }})</label>
        @endif

        @if($showCoachTabs)
            <input type="radio" id="user-tab-teams" name="user-tab">
            <label class="team-modal-tab" for="user-tab-teams">Equipos ({{ $teamAssignments->count() }})</label>

            <input type="radio" id="user-tab-matches" name="user-tab">
            <label class="team-modal-tab" for="user-tab-matches">Partidos ({{ $userMatches->count() }})</label>

            <input type="radio" id="user-tab-trainings" name="user-tab">
            <label class="team-modal-tab" for="user-tab-trainings">Entrenamientos ({{ $userTrainings->count() }})</label>
        @endif

        <div class="team-modal-panels">
            <div class="team-modal-panel" data-panel="user-info">
                <div class="card p-3 section-card">
                    <div class="user-info-grid">
                        <div class="user-info-item">
                            <div class="user-info-label">
                                <i class="fa-solid fa-user text-primary me-2"></i>
                                Nombre
                            </div>
                            <div class="user-info-value">{{ $user->name }} {{ $user->lastname }}</div>
                            <div class="user-info-sub">
                                <span class="meta-badge">{{ $user->username }}</span>
                            </div>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-label">
                                <i class="fa-solid fa-phone text-primary me-2"></i>
                                Contacto
                            </div>
                            <div class="user-info-value">{{ $user->email }}</div>
                            <div class="user-info-sub">{{ $user->phone }}</div>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-label">
                                <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                                Fecha de contrato
                            </div>
                            <div class="user-info-value">{{ $user->hired_date?->format('Y-m-d') ?? '-' }}</div>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-label">
                                <i class="fa-solid fa-id-badge text-primary me-2"></i>
                                Rol
                            </div>
                            <div class="user-info-value">{{ $user->role_label }}</div>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-label">
                                <i class="fa-solid fa-toggle-on text-primary me-2"></i>
                                Estado
                            </div>
                            <div class="user-info-value">
                                @if($user->status == \App\Models\User::ACTIVE)
                                    <span class="status-pill status-pill-success">Activo</span>
                                @else
                                    <span class="status-pill status-pill-muted">Inactivo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showVenueTab)
                <div class="team-modal-panel" data-panel="user-venues">
                    <div class="card p-3 section-card">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="fw-semibold">Sedes asociadas</div>
                            <span class="meta-badge">{{ $venues->count() }} sede{{ $venues->count() === 1 ? '' : 's' }}</span>
                        </div>
                        @if($venues->isEmpty())
                            <div class="empty-state-soft"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>Sin sedes asociadas.</div>
                        @else
                            <div class="row g-2 mt-2">
                                @foreach($venues as $venue)
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <div class="team-info-item user-mint-card h-100">
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
                </div>
            @endif

            @if($showCoachTabs)
                <div class="team-modal-panel" data-panel="user-teams">
                    <div class="card p-3 section-card">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="fw-semibold">Equipos</div>
                            <span class="meta-badge">{{ $teamAssignments->count() }} equipo{{ $teamAssignments->count() === 1 ? '' : 's' }}</span>
                        </div>
                        @if($teamAssignments->isEmpty())
                            <div class="empty-state-soft"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Sin equipos asociados.</div>
                        @else
                            <div class="row g-2 mt-2">
                                @foreach($teamAssignments as $assignment)
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <div class="team-info-item user-mint-card h-100">
                                            <span class="team-avatar-badge">
                                                <i class="fa-solid fa-shield"></i>
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $assignment->teamModel->name }}</div>
                                                <span class="meta-badge">
                                                    {{ $managerRoleLabels[$assignment->role] ?? 'Rol no definido' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="team-modal-panel" data-panel="user-matches">
                    <div class="card p-3 section-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">Partidos asociados</div>
                            <span class="meta-badge">{{ $userMatches->count() }} partido{{ $userMatches->count() === 1 ? '' : 's' }}</span>
                        </div>

                        @if($userMatches->isEmpty())
                            <div class="empty-state-soft"><i class="fa-solid fa-futbol" aria-hidden="true"></i>Sin partidos asociados.</div>
                        @else
                            <div class="row g-2">
                                @foreach($userMatches as $match)
                                    @php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
                                    @php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
                                    @php($statusLabel = \App\Models\MatchModel::statusOptions()[$match->match_status] ?? 'Sin estado')
                                    @php($isCompletedMatch = (int) $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED)
                                    @php($cardClass = match ((int) $match->match_status) {
                                        \App\Models\MatchModel::STATUS_SCHEDULED => 'user-match-card--scheduled',
                                        \App\Models\MatchModel::STATUS_COMPLETED => 'user-match-card--played',
                                        \App\Models\MatchModel::STATUS_CANCELLED => 'user-match-card--played',
                                        default => 'user-match-card--played',
                                    })
                                    @php($statusClass = match ((int) $match->match_status) {
                                        \App\Models\MatchModel::STATUS_SCHEDULED => 'user-match-badge--status-scheduled',
                                        \App\Models\MatchModel::STATUS_COMPLETED => 'user-match-badge--status-completed',
                                        \App\Models\MatchModel::STATUS_CANCELLED => 'user-match-badge--status-cancelled',
                                        default => 'user-match-badge--status-neutral',
                                    })
                                    @php($resultLabel = \App\Models\MatchModel::resultOptions()[$match->match_result] ?? 'Sin resultado')
                                    @php($resultClass = match ((int) $match->match_result) {
                                        \App\Models\MatchModel::RESULT_WIN => 'user-match-badge--result-win',
                                        \App\Models\MatchModel::RESULT_LOSS => 'user-match-badge--result-loss',
                                        \App\Models\MatchModel::RESULT_DRAW => 'user-match-badge--result-draw',
                                        default => 'user-match-badge--result-neutral',
                                    })
                                    <div class="col-12 col-lg-6">
                                        <div class="team-info-item team-match-card {{ $cardClass }} h-100">
                                            <span class="team-avatar-badge">
                                                <i class="fa-solid fa-futbol"></i>
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $teamModel?->name ?? 'Sin equipo' }} vs {{ $rivalModel?->name ?? 'Sin rival' }}</div>
                                                <div class="text-muted small">{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</div>
                                                <div class="user-match-badges mt-2">
                                                    <span class="user-match-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                    @if($isCompletedMatch)
                                                        <span class="user-match-badge {{ $resultClass }}">{{ $resultLabel }}</span>
                                                        <span class="user-match-badge user-match-badge--score">
                                                            <i class="fa-solid fa-hashtag"></i>
                                                            Marcador {{ $match->final_score ?: '-' }}
                                                        </span>
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

                <div class="team-modal-panel" data-panel="user-trainings">
                    <div class="card p-3 section-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">Entrenamientos asociados</div>
                            <span class="meta-badge">{{ $userTrainings->count() }} entrenamiento{{ $userTrainings->count() === 1 ? '' : 's' }}</span>
                        </div>

                        @if($userTrainings->isEmpty())
                            <div class="empty-state-soft"><i class="fa-solid fa-dumbbell" aria-hidden="true"></i>Sin entrenamientos asociados.</div>
                        @else
                            <div class="row g-2">
                                @foreach($userTrainings as $training)
                                    @php($teamModel = $training->relationLoaded('team') ? $training->getRelation('team') : null)
                                    @php($venueModel = $training->relationLoaded('venue') ? $training->getRelation('venue') : null)
                                    <div class="col-12 col-lg-6">
                                        <div class="team-info-item h-100">
                                            <span class="team-avatar-badge">
                                                <i class="fa-solid fa-dumbbell"></i>
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $training->name ?: 'Entrenamiento' }}</div>
                                                <div class="text-muted small">{{ $teamModel?->name ?? 'Sin equipo' }}</div>
                                                <div class="text-muted small">{{ $training->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                    <span class="meta-badge">{{ ((int) $training->status === \App\Models\Training::ACTIVE) ? 'Activo' : 'Inactivo' }}</span>
                                                    @if($training->duration)
                                                        <span class="meta-badge">{{ $training->duration }} min</span>
                                                    @endif
                                                    @if($venueModel?->name)
                                                        <span class="meta-badge">{{ $venueModel->name }}</span>
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
            @endif
        </div>
    </div>
</div>
