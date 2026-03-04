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
    <div class="user-info-grid">
        <div class="user-info-item">
            <div class="user-info-label">
                <i class="fa-solid fa-user text-primary me-2"></i>
                Nombre
            </div>
            <div class="user-info-value">{{ $user->name }} {{ $user->lastname }}</div>
            <div class="user-info-sub">
                <span class="meta-badge">{{ $user->username }}</span>
            </div>
        </div>
        <div class="user-info-item">
            <div class="user-info-label">
                <i class="fa-solid fa-phone text-primary me-2"></i>
                Contacto
            </div>
            <div class="user-info-value">{{ $user->email }}</div>
            <div class="user-info-sub">{{ $user->phone }}</div>
        </div>
        <div class="user-info-item">
            <div class="user-info-label">
                <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                Fecha de contrato
            </div>
            <div class="user-info-value">{{ $user->hired_date?->format('Y-m-d') ?? '-' }}</div>
        </div>
        <div class="user-info-item">
            <div class="user-info-label">
                <i class="fa-solid fa-id-badge text-primary me-2"></i>
                Rol
            </div>
            <div class="user-info-value">{{ $user->role_label }}</div>
        </div>
        <div class="user-info-item">
            <div class="user-info-label">
                <i class="fa-solid fa-toggle-on text-primary me-2"></i>
                Estado
            </div>
            <div class="user-info-value">
                @if($user->status == \App\Models\User::ACTIVE)
                    <span class="status-pill status-pill-success">Activo</span>
                @else
                    <span class="status-pill status-pill-muted">Inactivo</span>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $managerRoleLabels = [
        \App\Models\ManagerRoster::ROLE_PRIMARY_COACH => 'Entrenador principal',
        \App\Models\ManagerRoster::ROLE_ASSISTANT_COACH => 'Entrenador asistente',
    ];
@endphp

<div class="card p-3 section-card mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="fw-semibold">Sedes asociadas</div>
                <span class="meta-badge">{{ $venues->count() }} sede{{ $venues->count() === 1 ? '' : 's' }}</span>
            </div>
            @if($venues->isEmpty())
                <div class="text-muted">Sin sedes asociadas.</div>
            @else
                <div class="row g-2 mt-2">
                    @foreach($venues as $venue)
                        <div class="col-12 col-sm-6 col-lg-4">
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

        @if(in_array($user->role, [\App\Models\User::ROLE_COACH, \App\Models\User::ROLE_COORDINATOR], true))
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="fw-semibold">Equipos</div>
                    <span class="meta-badge">{{ $teamAssignments->count() }} equipo{{ $teamAssignments->count() === 1 ? '' : 's' }}</span>
                </div>
                @if($teamAssignments->isEmpty())
                    <div class="text-muted">Sin equipos asociados.</div>
                @else
                    <div class="row g-2 mt-2">
                        @foreach($teamAssignments as $assignment)
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="team-info-item h-100">
                                    <span class="team-avatar-badge">
                                        <i class="fa-solid fa-shield"></i>
                                    </span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $assignment->teamModel->name }}</div>
                                        <span class="meta-badge">
                                            {{ $assignment->teamModel->season }} {{ $assignment->teamModel->year }}
                                        </span>
                                        <div class="text-muted small">
                                            {{ $managerRoleLabels[$assignment->role] ?? 'Rol no definido' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
