@php($typeLabel = ($team->type ?? 0) === \App\Models\Team::TYPE_FORMATIVE ? 'Formativo' : 'Competitivo')

<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-shield"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información de la Plantilla</h3>
            <div class="text-muted small fw-bold">Consulta el detalle general de la plantilla</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Nombre</div>
            <div>{{ $team->name }}</div>
            <div class="mt-2">
                <span class="meta-badge">{{ $typeLabel }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Temporada</div>
            <div>{{ $team->season }} - {{ $team->year }}</div>
            <div class="mt-2">
                @if($team->status)
                    <span class="status-pill status-pill-success">Activa</span>
                @else
                    <span class="status-pill status-pill-muted">Inactiva</span>
                @endif
            </div>
        </div>
    </div>
</div>
