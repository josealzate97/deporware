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
        <div class="col-12">
            <div class="fw-semibold">Sedes asociadas</div>
            @if($team->venues->isEmpty())
                <div class="text-muted">Sin sedes asociadas.</div>
            @else
                <div class="row g-2 mt-2">
                    @foreach($team->venues as $venue)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="team-info-item h-100">
                                <span class="team-avatar-badge">
                                    <i class="fa-solid fa-building"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $venue->name }}</div>
                                    <span class="meta-badge">{{ $venue->city }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-12">
            <div class="fw-semibold">Entrenadores</div>
            @if(!$primaryCoach && !$assistantCoach)
                <div class="text-muted">Sin entrenadores asignados.</div>
            @else
                <div class="row g-2 mt-2">
                    @if($primaryCoach)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="team-info-item h-100">
                                <span class="team-avatar-badge">
                                    <i class="fa-solid fa-user-tie"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $primaryCoach->name }}</div>
                                    <span class="meta-badge">Entrenador principal</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($assistantCoach)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="team-info-item h-100">
                                <span class="team-avatar-badge">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $assistantCoach->name }}</div>
                                    <span class="meta-badge">Entrenador asistente</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
