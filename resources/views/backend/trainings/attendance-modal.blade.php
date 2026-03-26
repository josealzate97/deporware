<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Asistencia del Entrenamiento</h3>
            <div class="text-muted small fw-bold">Detalle de jugadores convocados</div>
        </div>
    </div>
</div>

@php($positionOptions = \App\Models\Player::positionOptions())
@php($attendanceList = $training->attendance ?? collect())
@php($attendanceCount = $attendanceList->count())
@php($activeRosterList = $activeRosters ?? collect())
@php($absentRosterList = $absentRosters ?? collect())
@php($absentCount = $absentRosterList->count())
@php($totalCallUp = $activeRosterList->count())

<div class="card p-3 section-card training-attendance-modal-card">
    <div class="training-attendance-overview">
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-dumbbell"></i>
                Entrenamiento
            </div>
            <div class="training-attendance-stat-value">{{ $training->name ?: '-' }}</div>
        </div>
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-shield-halved"></i>
                Equipo
            </div>
            <div class="training-attendance-stat-value">{{ $training->getRelationValue('team')?->name ?? '-' }}</div>
        </div>
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-calendar-days"></i>
                Fecha
            </div>
            <div class="training-attendance-stat-value">{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-user-check"></i>
                Asistentes
            </div>
            <div class="training-attendance-stat-value">{{ $attendanceCount }}</div>
        </div>
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-users"></i>
                Convocados
            </div>
            <div class="training-attendance-stat-value">{{ $totalCallUp }}</div>
        </div>
        <div class="training-attendance-stat">
            <div class="training-attendance-stat-label">
                <i class="fa-solid fa-user-xmark"></i>
                Inasistentes
            </div>
            <div class="training-attendance-stat-value">{{ $absentCount }}</div>
        </div>
    </div>

    <div class="divider my-3"></div>

    <div class="training-attendance-tabs-wrap">
        <div class="training-attendance-tabs">
            <input type="radio" id="attendance-tab-present" name="attendance-tabs" checked>
            <label for="attendance-tab-present">
                <i class="fa-solid fa-circle-check"></i>
                Asistencia ({{ $attendanceCount }})
            </label>

            <input type="radio" id="attendance-tab-absent" name="attendance-tabs">
            <label for="attendance-tab-absent">
                <i class="fa-solid fa-circle-xmark"></i>
                No asistencia ({{ $absentCount }})
            </label>

            <div class="training-attendance-tab-panels">
                <div class="training-attendance-tab-panel" data-panel="present">
                    <div class="training-attendance-list-title">
                        <i class="fa-solid fa-user-check"></i>
                        Jugadores asistentes
                    </div>
                    <div class="row g-3">
                        @forelse($attendanceList as $attendance)
                            @php($player = $attendance->getRelationValue('player'))
                            @php($roster = $player?->getRelationValue('activeRoster'))
                            @php($dorsal = $roster?->dorsal ?? $player?->dorsal)
                            @php($positionId = $roster?->position ?? $player?->primary_position ?? $player?->position)
                            @php($positionLabel = $positionOptions[$positionId] ?? '-')
                            <div class="col-12 col-md-6">
                                <div class="training-attendance-card h-100">
                                    <div class="training-attendance-avatar">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div class="training-attendance-content flex-grow-1">
                                        <div class="training-attendance-name">
                                            {{ trim(($player?->name ?? 'Jugador') . ' ' . ($player?->lastname ?? '')) }}
                                        </div>
                                        <div class="training-attendance-meta">
                                            <span class="training-attendance-chip training-attendance-chip-number">
                                                <i class="fa-solid fa-hashtag"></i>
                                                {{ $dorsal ?? '-' }}
                                            </span>
                                            <span class="training-attendance-chip">
                                                <i class="fa-solid fa-compass"></i>
                                                {{ $positionLabel }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="training-attendance-state">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Asistió
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="training-attendance-empty">
                                    No hay jugadores registrados en la asistencia de este entrenamiento.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="training-attendance-tab-panel" data-panel="absent">
                    <div class="training-attendance-list-title">
                        <i class="fa-solid fa-user-xmark"></i>
                        Jugadores que no asistieron
                    </div>
                    <div class="row g-3">
                        @forelse($absentRosterList as $roster)
                            @php($player = $roster->getRelationValue('player'))
                            @php($dorsal = $roster->dorsal ?? $player?->dorsal)
                            @php($positionId = $roster->position ?? $player?->primary_position ?? $player?->position)
                            @php($positionLabel = $positionOptions[$positionId] ?? '-')
                            <div class="col-12 col-md-6">
                                <div class="training-attendance-card training-attendance-card-absent h-100">
                                    <div class="training-attendance-avatar">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div class="training-attendance-content flex-grow-1">
                                        <div class="training-attendance-name">
                                            {{ trim(($player?->name ?? 'Jugador') . ' ' . ($player?->lastname ?? '')) }}
                                        </div>
                                        <div class="training-attendance-meta">
                                            <span class="training-attendance-chip training-attendance-chip-number">
                                                <i class="fa-solid fa-hashtag"></i>
                                                {{ $dorsal ?? '-' }}
                                            </span>
                                            <span class="training-attendance-chip">
                                                <i class="fa-solid fa-compass"></i>
                                                {{ $positionLabel }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="training-attendance-state training-attendance-state-absent">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        No asistió
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="training-attendance-empty">
                                    No se registran inasistentes en este entrenamiento.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
