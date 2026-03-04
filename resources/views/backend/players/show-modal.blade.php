<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-people-group"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Jugador</h3>
            <div class="text-muted small fw-bold">Consulta los datos principales del jugador</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Jugador</div>
            <div>{{ $player->name }} {{ $player->lastname }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Contacto</div>
            <div>{{ $player->email ?? '-' }}</div>
            <div>{{ $player->phone ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Estado</div>
            @if($player->status == \App\Models\Player::ACTIVE)
                <span class="status-pill status-pill-success">Activo</span>
            @else
                <span class="status-pill status-pill-muted">Inactivo</span>
            @endif
        </div>
    </div>
</div>
