@extends('backend.layouts.main')

@section('title', 'Panel Global')

@push('styles')
    @vite(['resources/css/modules/dashboard.css'])
@endpush

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
        <div class="dashboard-summary-grid mt-4 mb-4">
            <div class="dashboard-summary-card dashboard-summary-card--purple">
                <div class="dashboard-summary-card__icon"><i class="fa-solid fa-building"></i></div>
                <div class="dashboard-summary-card__body">
                    <div class="dashboard-summary-card__label">Escuelas activas</div>
                    <div class="dashboard-summary-card__value">{{ $activeTenants }}</div>
                    <div class="dashboard-summary-card__meta">de {{ $totalTenants }} registradas</div>
                </div>
            </div>
            <div class="dashboard-summary-card dashboard-summary-card--success">
                <div class="dashboard-summary-card__icon"><i class="fa-solid fa-users"></i></div>
                <div class="dashboard-summary-card__body">
                    <div class="dashboard-summary-card__label">Usuarios totales</div>
                    <div class="dashboard-summary-card__value">{{ $totalUsers }}</div>
                    <div class="dashboard-summary-card__meta">en todas las escuelas</div>
                </div>
            </div>
            <div class="dashboard-summary-card dashboard-summary-card--primary">
                <div class="dashboard-summary-card__icon"><i class="fa-solid fa-people-group"></i></div>
                <div class="dashboard-summary-card__body">
                    <div class="dashboard-summary-card__label">Jugadores totales</div>
                    <div class="dashboard-summary-card__value">{{ $totalPlayers }}</div>
                    <div class="dashboard-summary-card__meta">{{ $totalTeams }} plantillas registradas</div>
                </div>
            </div>
            <div class="dashboard-summary-card dashboard-summary-card--warning">
                <div class="dashboard-summary-card__icon"><i class="fa-solid fa-futbol"></i></div>
                <div class="dashboard-summary-card__body">
                    <div class="dashboard-summary-card__label">Partidos registrados</div>
                    <div class="dashboard-summary-card__value">{{ $totalMatches }}</div>
                    <div class="dashboard-summary-card__meta">en toda la plataforma</div>
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
                                <td><code class="slug-badge">{{ $t->slug }}</code></td>
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
                                        <button type="submit" class="btn btn-sm btn-table-purple me-1" title="Acceder a esta escuela">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('tenants.edit', $t->id) }}" class="btn btn-sm btn-table-green me-1" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenants.activate', $t->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ $t->status ? 'btn-table-red' : 'btn-table-green' }}"
                                            title="{{ $t->status ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $t->status ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center gap-3 text-muted">
                                        <i class="fa-solid fa-building fa-2x opacity-25"></i>
                                        <div class="text-center">
                                            <div class="fw-semibold mb-1">Aún no hay escuelas registradas</div>
                                            <div class="small">Crea la primera escuela para comenzar a gestionar el sistema.</div>
                                        </div>
                                        <a href="{{ route('tenants.new') }}" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-plus-circle me-1"></i> Crear primera escuela
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
