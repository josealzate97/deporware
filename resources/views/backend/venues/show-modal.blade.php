<div class="show-modal-mint">
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
        <div class="entity-info-grid">
            <div class="entity-info-item">
                <div class="entity-info-label">
                    <i class="fa-solid fa-building text-primary me-2"></i>
                    Sede
                </div>
                <div class="entity-info-value">{{ $venue->name }}</div>
                <div class="entity-info-sub">
                    <span class="meta-badge">{{ $venue->city }}</span>
                </div>
            </div>
            <div class="entity-info-item">
                <div class="entity-info-label">
                    <i class="fa-solid fa-location-dot text-primary me-2"></i>
                    Dirección
                </div>
                <div class="entity-info-value">{{ $venue->address }}</div>
                <div class="entity-info-sub">
                    @if($venue->status)
                        <span class="status-pill status-pill-success">Activa</span>
                    @else
                        <span class="status-pill status-pill-muted">Inactiva</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="fw-semibold">Equipos asociados</div>
                <span class="meta-badge">{{ $venue->teams->count() }} equipo{{ $venue->teams->count() === 1 ? '' : 's' }}</span>
            </div>
            @if($venue->teams->isEmpty())
                <div class="venue-empty-associations mt-2">
                    <span class="venue-empty-associations-icon" aria-hidden="true">
                        <i class="fa-solid fa-shield"></i>
                    </span>
                    <div>
                        <div class="venue-empty-associations-title">Sin equipos asociados</div>
                        <div class="venue-empty-associations-text">Esta sede aun no tiene equipos vinculados.</div>
                    </div>
                </div>
            @else
                <div class="row g-2 mt-2">
                    @foreach($venue->teams as $team)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="team-info-item h-100">
                                <span class="team-avatar-badge">
                                    <i class="fa-solid fa-shield"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $team->name }}</div>
                                    <span class="meta-badge">{{ $team->year }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
