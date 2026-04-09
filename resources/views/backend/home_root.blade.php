@extends('backend.layouts.main')

@section('title', 'Panel Global')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb')
        @endpush

        <div class="card p-4 section-hero surface-gradient-day mb-4">
            <div class="d-flex align-items-start gap-3">
                <div class="section-hero-icon dashboard-hero-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div>
                    <div class="dashboard-eyebrow">Super Admin</div>
                    <h2 class="fw-bold mb-1">Panel Global</h2>
                    <div class="text-muted small fw-bold">Vista general de todas las escuelas registradas en el sistema</div>
                </div>
            </div>
        </div>

        {{-- Tarjetas resumen --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100 p-3 text-center">
                    <div class="fs-3 fw-bold text-primary">{{ $activeTenants }}</div>
                    <div class="small text-muted mt-1">Escuelas activas</div>
                    <div class="small text-muted">de {{ $totalTenants }} registradas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 p-3 text-center">
                    <div class="fs-3 fw-bold text-success">{{ $totalUsers }}</div>
                    <div class="small text-muted mt-1">Usuarios totales</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 p-3 text-center">
                    <div class="fs-3 fw-bold" style="color:var(--brand-blue)">{{ $totalPlayers }}</div>
                    <div class="small text-muted mt-1">Jugadores totales</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 p-3 text-center">
                    <div class="fs-3 fw-bold text-warning">{{ $totalMatches }}</div>
                    <div class="small text-muted mt-1">Partidos registrados</div>
                </div>
            </div>
        </div>

        {{-- Lista de escuelas --}}
        <div class="card p-0 overflow-hidden">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-building me-2 text-muted"></i>Escuelas</h6>
                <a href="{{ route('tenants.new') }}" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-plus-circle me-1"></i> Nueva Escuela
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th class="text-center">Usuarios</th>
                            <th class="text-center">Plantillas</th>
                            <th class="text-center">Jugadores</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->name }}</td>
                                <td><code class="small">{{ $t->slug }}</code></td>
                                <td class="text-center">{{ $t->users_count }}</td>
                                <td class="text-center">{{ $t->teams_count }}</td>
                                <td class="text-center">{{ $t->players_count }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $t->status === \App\Models\Tenant::ACTIVE ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $t->status === \App\Models\Tenant::ACTIVE ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('root.tenant.switch') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="Administrar esta escuela">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('tenants.edit', $t->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenants.activate', $t->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $t->status ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $t->status ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $t->status ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No hay escuelas registradas aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
