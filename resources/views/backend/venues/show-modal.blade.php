<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-building-circle-check"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información de la Sede</h3>
            <div class="text-muted small fw-bold">Consulta los detalles y estado de la sede</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Sede</div>
            <div>{{ $venue->name }}</div>
            <div class="mt-2">
                <span class="meta-badge">{{ $venue->city }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Dirección</div>
            <div>{{ $venue->address }}</div>
            <div class="mt-2">
                @if($venue->status)
                    <span class="status-pill status-pill-success">Activa</span>
                @else
                    <span class="status-pill status-pill-muted">Inactiva</span>
                @endif
            </div>
        </div>
    </div>
</div>
