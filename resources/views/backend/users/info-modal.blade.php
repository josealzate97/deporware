<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-user"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Usuario</h3>
            <div class="text-muted small fw-bold">Consulta los datos principales del personal</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Nombre</div>
            <div>{{ $user->name }} {{ $user->lastname }}</div>
            <div class="mt-2">
                <span class="meta-badge">{{ $user->username }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Contacto</div>
            <div>{{ $user->email }}</div>
            <div>{{ $user->phone }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Rol</div>
            <div>{{ $user->role_label }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Estado</div>
            @if($user->status == \App\Models\User::ACTIVE)
                <span class="status-pill status-pill-success">Activo</span>
            @else
                <span class="status-pill status-pill-muted">Inactivo</span>
            @endif
        </div>
    </div>
</div>
