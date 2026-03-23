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

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Entrenamiento</div>
            <div>{{ $training->name }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Equipo</div>
            <div>{{ $training->getRelationValue('team')?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Fecha</div>
            <div>{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Asistentes</div>
            <div>{{ $training->attendance?->count() ?? 0 }}</div>
        </div>
    </div>

    <div class="divider my-3"></div>

    <div class="row g-3">
        @forelse(($training->attendance ?? []) as $attendance)
            <div class="col-12 col-md-6">
                <div class="training-attendance-card h-100">
                    <div class="training-attendance-content">
                        <div class="training-attendance-name">
                            {{ $attendance->player?->name ?? 'Jugador' }} {{ $attendance->player?->lastname ?? '' }}
                        </div>
                        <div class="training-attendance-meta">
                            <span>#{{ $attendance->player?->dorsal ?? '-' }}</span>
                            <span>{{ $attendance->player?->position ?? '-' }}</span>
                        </div>
                    </div>
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