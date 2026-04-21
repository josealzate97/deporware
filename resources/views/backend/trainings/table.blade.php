<div class="table-responsive">
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
            @forelse($trainings as $training)
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
                    <td>
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
                    <td>
                        @if(!empty($coachName))
                            <span class="training-coach-badge">
                                <i class="fa-solid fa-user-tie"></i>
                                {{ $coachName }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $training->duration_label ?? '-' }}</td>
                    <td class="text-center">
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
                    <td class="text-center">
                        <button type="button" class="btn training-attendance-btn"
                            @click="openModal('{{ route('trainings.show', $training->id) }}?modal=attendance')"
                            aria-label="Ver asistentes de {{ $training->name }}">
                            <i class="fa-solid fa-user-check"></i>
                            <span class="training-attendance-ratio-inline">{{ $attendanceCount }}/{{ $calledUpCount }}</span>
                        </button>
                    </td>
                    <td>
                        @if($training->status == \App\Models\Training::ACTIVE)
                            <span class="status-pill status-pill-success">Activo</span>
                        @else
                            <span class="status-pill status-pill-muted">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1 training-actions-group">
                            <button type="button" class="btn btn-icon training-action-btn training-action-btn-info"
                                @click="openModal('{{ route('trainings.show', $training->id) }}?modal=1')"
                                aria-label="Ver información de {{ $training->name }}" title="Ver información">
                                <i class="fas fa-circle-info"></i>
                            </button>
                            @if($isCoordinator ?? false)
                                <a href="{{ route('trainings.edit', $training->id) }}#training-observations" class="btn btn-icon training-action-btn training-action-btn-warning"
                                    aria-label="Observaciones de {{ $training->name }}" title="Observaciones">
                                    <i class="fa-solid fa-note-sticky"></i>
                                </a>
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
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay entrenamientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
