<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-futbol"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Partido</h3>
            <div class="text-muted small fw-bold">Consulta la información general del encuentro</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Fecha</div>
            <div>{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Equipo</div>
            @php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
            <div>{{ $teamModel?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Rival</div>
            @php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
            <div>{{ $rivalModel?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Estado</div>
            <span class="status-pill {{ $match->match_status ? 'status-pill-success' : 'status-pill-muted' }}">
                {{ $match->match_status ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
    </div>
</div>
