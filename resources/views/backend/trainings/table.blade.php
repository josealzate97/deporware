@if($trainings->count() > 0)
<div class="table-responsive responsive-stack-table trainings-card-table">
    <table class="table table-borderless align-middle section-table">
        <thead>
            <tr>
                <th>Entrenamiento</th>
                <th>Entrenador</th>
                <th>Duración</th>
                <th class="text-center">Sede</th>
                <th class="text-center">Asistentes</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainings as $training)
                @php($attendanceCount = (int) ($training->attendance_count ?? 0))
                @php($calledUpCount = (int) ($training->called_up_count ?? 0))
                @php($teamName = $training->getRelationValue('team')?->name ?? 'Sin equipo')
                @php($venueModel = $training->getRelationValue('venue'))
                @php($teamModel = $training->getRelationValue('team'))
                @php($managerRosters = collect($teamModel?->getRelationValue('managerRosters') ?? []))
                @php($coachRoster = $managerRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_PRIMARY_COACH))
                @php($assistantCoachRoster = $managerRosters->firstWhere('role', \App\Models\ManagerRoster::ROLE_ASSISTANT_COACH))
                @php($selectedCoach = $coachRoster ?: $assistantCoachRoster)
                @php($coachName = $selectedCoach?->getRelationValue('user')?->name)
                <tr data-id="{{ $training->id }}">
                    <td data-label="Entrenamiento">
                        <div class="training-main-cell">
                            <div class="training-main-name">{{ $training->name }}</div>
                            <div class="training-main-meta">
                                <span class="training-team-badge">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    {{ $teamName }}
                                </span>
                                <span class="training-main-date">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    {{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Entrenador">
                        @if(!empty($coachName))
                            <span class="training-coach-badge">
                                <i class="fa-solid fa-user-tie"></i>
                                {{ $coachName }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td data-label="Duración">{{ $training->duration_label ?? '-' }}</td>
                    <td class="text-center" data-label="Sede">
                        @if($venueModel)
                            <button type="button" class="btn btn-sm training-venue-link"
                                @click="openModal('{{ route('venues.show', $venueModel->id) }}?modal=1')"
                                aria-label="Ver sede {{ $venueModel->name }}">
                                <i class="fa-solid fa-location-dot me-1"></i>
                                {{ $venueModel->name }}
                            </button>
                        @elseif(!empty($training->location))
                            <span class="training-venue-fallback">{{ $training->location }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center" data-label="Asistentes">
                        <button type="button" class="btn training-attendance-btn"
                            @click="openModal('{{ route('trainings.show', $training->id) }}?modal=attendance')"
                            aria-label="Ver asistentes de {{ $training->name }}">
                            <i class="fa-solid fa-user-check"></i>
                            <span class="training-attendance-ratio-inline">{{ $attendanceCount }}/{{ $calledUpCount }}</span>
                        </button>
                    </td>
                    <td data-label="Estado">
                        @if($training->status == \App\Models\Training::ACTIVE)
                            <span class="status-pill status-pill-success">Activo</span>
                        @else
                            <span class="status-pill status-pill-muted">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end trainings-card-actions" data-label="Acciones">
                        <div class="d-inline-flex align-items-center gap-1 training-actions-group">
                            <button type="button" class="btn btn-icon training-action-btn training-action-btn-info"
                                @click="openModal('{{ route('trainings.show', $training->id) }}?modal=1')"
                                aria-label="Ver información de {{ $training->name }}" title="Ver información">
                                <i class="fas fa-circle-info"></i>
                            </button>
                            @if($isCoordinator ?? false)
                                <button type="button" class="btn btn-icon training-action-btn training-action-btn-warning"
                                    @click="openModal('{{ route('trainings.show', $training->id) }}?modal=observations')"
                                    aria-label="Observaciones de {{ $training->name }}" title="Observaciones">
                                    <i class="fa-solid fa-note-sticky"></i>
                                </button>
                            @endif
                            <a href="{{ route('trainings.edit', $training->id) }}" class="btn btn-icon training-action-btn training-action-btn-edit"
                                aria-label="Editar {{ $training->name }}" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('trainings.destroy', $training->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon training-action-btn training-action-btn-danger"
                                    onclick="return confirm('¿Deseas desactivar este entrenamiento?')"
                                    aria-label="Desactivar {{ $training->name }}" title="Desactivar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="trainings-list-empty" role="status" aria-live="polite">
    <i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>
    <div class="trainings-list-empty-title">No hay entrenamientos registrados.</div>
    <div class="trainings-list-empty-subtitle">Crea un entrenamiento para comenzar a gestionar tus sesiones.</div>
    <a href="{{ route('trainings.new') }}" class="btn btn-success trainings-list-empty-cta">
        <i class="fa-solid fa-plus-circle me-2"></i> Crear Entrenamiento
    </a>
</div>
@endif
