<div class="table-responsive">
    <table class="table table-borderless align-middle section-table">
        <thead>
            <tr>
                <th>Entrenamiento</th>
                <th>Equipo</th>
                <th>Fecha</th>
                <th>Duración</th>
                <th>Sede</th>
                <th>Asistentes</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trainings as $training)
                <tr data-id="{{ $training->id }}">
                    <td class="fw-bold">{{ $training->name }}</td>
                    <td>{{ $training->getRelationValue('team')?->name ?? '-' }}</td>
                    <td>{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $training->duration ? $training->duration . ' min' : '-' }}</td>
                    <td>{{ $training->venue?->name ?? '-' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $training->attendance_count ?? 0 }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                @click="openModal('{{ route('trainings.show', $training->id) }}?modal=attendance')"
                                aria-label="Ver asistentes de {{ $training->name }}">
                                Ver más
                            </button>
                        </div>
                    </td>
                    <td>
                        @if($training->status == \App\Models\Training::ACTIVE)
                            <span class="status-pill status-pill-success">Activo</span>
                        @else
                            <span class="status-pill status-pill-muted">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1">
                            <button type="button" class="btn btn-icon text-primary"
                                @click="openModal('{{ route('trainings.show', $training->id) }}?modal=1')"
                                aria-label="Ver información de {{ $training->name }}" title="Ver información">
                                <i class="fas fa-circle-info"></i>
                            </button>
                            <a href="{{ route('trainings.edit', $training->id) }}" class="btn btn-icon text-success"
                                aria-label="Editar {{ $training->name }}" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('trainings.destroy', $training->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon text-danger"
                                    onclick="return confirm('¿Deseas desactivar este entrenamiento?')"
                                    aria-label="Desactivar {{ $training->name }}" title="Desactivar">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No hay entrenamientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
