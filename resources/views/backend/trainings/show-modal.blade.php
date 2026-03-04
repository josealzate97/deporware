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

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Entrenamiento</div>
            <div>{{ $training->name }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Equipo</div>
            <div>{{ $training->team?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Estado</div>
            @if($training->status == \App\Models\Training::ACTIVE)
                <span class="status-pill status-pill-success">Activo</span>
            @else
                <span class="status-pill status-pill-muted">Inactivo</span>
            @endif
        </div>
    </div>
</div>
