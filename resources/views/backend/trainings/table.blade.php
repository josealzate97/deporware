<div class="table-responsive">
    <table class="table table-borderless align-middle section-table">
        <thead>
            <tr>
                <th>Entrenamiento</th>
                <th>Equipo</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trainings as $training)
                <tr data-id="{{ $training->id }}">
                    <td class="fw-bold">{{ $training->name }}</td>
                    <td>{{ $training->team?->name ?? '-' }}</td>
                    <td>
                        @if($training->status == \App\Models\Training::ACTIVE)
                            <span class="status-pill status-pill-success">Activo</span>
                        @else
                            <span class="status-pill status-pill-muted">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-icon text-primary"
                            @click="openModal('{{ route('trainings.show', $training->id) }}?modal=1')"
                            aria-label="Ver información de {{ $training->name }}" title="Ver información">
                            <i class="fas fa-circle-info"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No hay entrenamientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
