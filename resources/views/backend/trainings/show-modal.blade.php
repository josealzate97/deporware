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

<div class="card p-3 section-card">
    <div class="match-tabs">
        <input type="radio" id="training-tab-info" name="training-tabs" checked>
        <label for="training-tab-info">Info general</label>

        <input type="radio" id="training-tab-goals" name="training-tabs">
        <label for="training-tab-goals">Objetivos</label>

        <input type="radio" id="training-tab-attendance" name="training-tabs">
        <label for="training-tab-attendance">Asistencias</label>

        <input type="radio" id="training-tab-documents" name="training-tabs">
        <label for="training-tab-documents">Documentos</label>

        <div class="match-tab-panels w-100 mt-3">
            <div class="match-tab-panel" data-panel="training-info">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label">Entrenamiento</div>
                        <div class="match-info-value">{{ $training->name }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Equipo</div>
                        <div class="match-info-value">{{ $training->getRelationValue('team')?->name ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Fecha</div>
                        <div class="match-info-value">{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Duración</div>
                        <div class="match-info-value">{{ $training->duration ? $training->duration . ' min' : '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Sede</div>
                        <div class="match-info-value">{{ $training->venue?->name ?? '-' }}</div>
                        <div class="match-info-sub">Locación: {{ $training->location ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Estado</div>
                        <div class="match-info-value">
                            <span class="status-pill {{ $training->status == \App\Models\Training::ACTIVE ? 'status-pill-success' : 'status-pill-muted' }}">
                                {{ $statusOptions[$training->status] ?? '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Momento</div>
                        <div class="match-info-value">{{ $momentOptions[$training->moment] ?? '-' }}</div>
                    </div>
                </div>

                <div class="match-info-item mt-3">
                    <div class="match-info-label">Notas</div>
                    <div class="match-info-sub">{{ $training->notes ?: 'Sin notas registradas.' }}</div>
                </div>
            </div>

            <div class="match-tab-panel" data-panel="training-goals">
                <div class="match-info-grid">
                    <div class="match-info-item">
                        <div class="match-info-label">Objetivo táctico</div>
                        <div class="match-info-value">{{ $tacticOptions[$training->tactic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Objetivo físico</div>
                        <div class="match-info-value">{{ $fisicOptions[$training->fisic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Objetivo técnico</div>
                        <div class="match-info-value">{{ $tecnicOptions[$training->tecnic_obj] ?? '-' }}</div>
                    </div>
                    <div class="match-info-item">
                        <div class="match-info-label">Objetivo psicológico</div>
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
                        <div class="match-info-label">Documento del entrenamiento</div>
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
        </div>
    </div>
</div>
