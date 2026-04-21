<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fas fa-dumbbell"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Entrenamiento</h3>
            <div class="text-muted small fw-bold">Consulta el detalle general de la sesión</div>
        </div>
    </div>
</div>

@php($tacticOptions = \App\Models\Training::tacticObjectivesOptions())
@php($fisicOptions = \App\Models\Training::fisicObjectivesOptions())
@php($tecnicOptions = \App\Models\Training::tecnichObjectivesOptions())
@php($psychoOptions = \App\Models\Training::psychoObjectivesOptions())
@php($momentOptions = \App\Models\Training::momentOptions())
@php($statusOptions = \App\Models\Training::statusOptions())
@php($positionOptions = \App\Models\Player::positionOptions())
@php($documentUrl = $training->document ? \Illuminate\Support\Facades\Storage::url($training->document) : null)
@php($teamModel = $training->getRelationValue('team'))
@php($coachRosters = collect($teamModel?->getRelationValue('managerRosters') ?? []))
@php($primaryCoachRoster = $coachRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_PRIMARY_COACH))
@php($assistantCoachRoster = $coachRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_ASSISTANT_COACH))
@php($selectedCoach = $primaryCoachRoster ?: $assistantCoachRoster)
@php($coachName = $selectedCoach?->getRelationValue('user')?->name)
@php($venueModel = $training->getRelationValue('venue'))
@php($observations = $training->getRelationValue('observations') ?? collect())

<div class="card p-3 section-card">
    <div class="match-tabs">
        <input type="radio" id="training-tab-info" name="training-tabs" checked>
        <label for="training-tab-info">
            <i class="fa-solid fa-circle-info match-tab-label-icon"></i>
            Info general
        </label>

        <input type="radio" id="training-tab-goals" name="training-tabs">
        <label for="training-tab-goals">
            <i class="fa-solid fa-bullseye match-tab-label-icon"></i>
            Objetivos
        </label>

        <input type="radio" id="training-tab-attendance" name="training-tabs">
        <label for="training-tab-attendance">
            <i class="fa-solid fa-user-check match-tab-label-icon"></i>
            Asistencias
        </label>

        <input type="radio" id="training-tab-documents" name="training-tabs">
        <label for="training-tab-documents">
            <i class="fa-solid fa-folder-open match-tab-label-icon"></i>
            Documentos
        </label>

        <input type="radio" id="training-tab-observations" name="training-tabs">
        <label for="training-tab-observations">
            <i class="fa-solid fa-note-sticky match-tab-label-icon"></i>
            Observaciones
        </label>

        <div class="match-tab-panels w-100 mt-3">
            <div class="match-tab-panel" data-panel="training-info">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-dumbbell match-info-label-icon"></i>Entrenamiento</div>
                        <div class="match-info-value">{{ $training->name }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-shield-halved match-info-label-icon"></i>Equipo</div>
                        <div class="match-info-value">{{ $training->getRelationValue('team')?->name ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-user-tie match-info-label-icon"></i>Entrenador</div>
                        <div class="match-info-value">{{ $coachName ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-calendar-days match-info-label-icon"></i>Fecha</div>
                        <div class="match-info-value">{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-stopwatch match-info-label-icon"></i>Duración</div>
                        <div class="match-info-value">{{ $training->duration ? $training->duration . ' min' : '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Sede</div>
                        <div class="match-info-value">{{ $venueModel?->name ?? '-' }}</div>
                        <div class="match-info-sub">Locación: {{ $training->location ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-signal match-info-label-icon"></i>Estado</div>
                        <div class="match-info-value">
                            <span class="status-pill {{ $training->status == \App\Models\Training::ACTIVE ? 'status-pill-success' : 'status-pill-muted' }}">
                                {{ $statusOptions[$training->status] ?? '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-clock match-info-label-icon"></i>Momento</div>
                        <div class="match-info-value">{{ $momentOptions[$training->moment] ?? '-' }}</div>
                    </div>
                </div>

                <div class="match-info-item mt-3">
                    <div class="match-info-label"><i class="fa-solid fa-note-sticky match-info-label-icon"></i>Notas</div>
                    <div class="match-info-sub">{{ $training->notes ?: 'Sin notas registradas.' }}</div>
                </div>
            </div>

            <div class="match-tab-panel" data-panel="training-goals">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-user-tie match-info-label-icon"></i>Entrenador</div>
                        <div class="match-info-value">{{ $coachName ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-chess-knight match-info-label-icon"></i>Objetivo táctico</div>
                        <div class="match-info-value">{{ $tacticOptions[$training->tactic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-heart-pulse match-info-label-icon"></i>Objetivo físico</div>
                        <div class="match-info-value">{{ $fisicOptions[$training->fisic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-futbol match-info-label-icon"></i>Objetivo técnico</div>
                        <div class="match-info-value">{{ $tecnicOptions[$training->tecnic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label"><i class="fa-solid fa-brain match-info-label-icon"></i>Objetivo psicológico</div>
                        <div class="match-info-value">{{ $psychoOptions[$training->pyscho_obj] ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="match-tab-panel" data-panel="training-attendance">
                @if(($training->attendance ?? collect())->isEmpty())
                    <div class="text-muted">No hay jugadores registrados en la asistencia de este entrenamiento.</div>
                @else
                    <div class="row g-3">
                        @foreach($training->attendance as $attendance)
                            @php($player = $attendance->getRelationValue('player'))
                            @php($roster = $player?->getRelationValue('activeRoster'))
                            @php($dorsal = $roster?->dorsal ?? $player?->dorsal)
                            @php($positionId = $roster?->position ?? $player?->primary_position ?? $player?->position)
                            @php($positionLabel = $positionOptions[$positionId] ?? '-')
                            <div class="col-12 col-md-6">
                                <div class="training-attendance-card h-100">
                                    <div class="training-attendance-content">
                                        <div class="training-attendance-name">
                                            {{ $player?->name ?? 'Jugador' }} {{ $player?->lastname ?? '' }}
                                        </div>
                                        <div class="training-attendance-meta">
                                            <span>#{{ $dorsal ?? '-' }}</span>
                                            <span>{{ $positionLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="match-tab-panel" data-panel="training-documents">
                @if($documentUrl)
                    <div class="match-info-item">
                        <div class="match-info-label" style="font-size:1rem;font-weight:700;"><i class="fa-solid fa-file-lines match-info-label-icon"></i>Documento del entrenamiento</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                            <a href="{{ route('trainings.view.document', $training->id) }}" target="_blank" rel="noopener" class="btn btn-sm match-file-action-btn">
                                <i class="fa-solid fa-eye me-1"></i> Visualizar
                            </a>
                            <a href="{{ route('trainings.download.document', $training->id) }}" class="btn btn-sm match-file-download-btn">
                                <i class="fa-solid fa-download me-1"></i> Descargar
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-muted">No hay documento cargado.</div>
                @endif
            </div>

            <div class="match-tab-panel" data-panel="training-observations">
                @if($observations->isEmpty())
                    <div class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-muted">
                        <i class="fa-regular fa-note-sticky" style="font-size:2rem;opacity:.4;"></i>
                        <span class="fw-semibold">Sin observaciones</span>
                        <span class="small">Aún no se han registrado observaciones para este entrenamiento.</span>
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($observations as $observation)
                            <div class="match-info-item">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <div class="match-info-label">
                                        <i class="fa-solid fa-user-pen match-info-label-icon"></i>
                                        {{ $observation->author?->name ?? 'Coordinador' }}
                                    </div>
                                    <span class="match-info-sub">{{ $observation->updated_at?->format('d/m/Y H:i') ?? '-' }}</span>
                                </div>
                                <div class="match-info-sub mt-2">{{ $observation->note }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
