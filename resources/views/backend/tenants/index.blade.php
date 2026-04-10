@extends('backend.layouts.main')

@section('title', 'Escuelas')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => ['route' => 'tenants.index', 'icon' => 'fa-solid fa-building', 'label' => 'Escuelas']
            ])
        @endpush

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card p-4 section-hero mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0">Escuelas</h2>
                        <div class="text-muted small fw-bold">Gestiona los tenants registrados en el sistema</div>
                    </div>
                </div>
                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('tenants.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Nueva Escuela
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-0 overflow-hidden section-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Slug de acceso</th>
                            <th class="text-center">Usuarios</th>
                            <th class="text-center">Plantillas</th>
                            <th class="text-center">Jugadores</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Creada</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $t)
                            <tr>
                                <td class="text-muted small">{{ $t->number }}</td>
                                <td class="fw-semibold">{{ $t->name }}</td>
                                <td>
                                    <code class="slug-badge">{{ $t->slug }}</code>
                                </td>
                                <td class="text-center">{{ $t->users_count }}</td>
                                <td class="text-center">{{ $t->teams_count }}</td>
                                <td class="text-center">{{ $t->players_count }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill {{ $t->status === \App\Models\Tenant::ACTIVE ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $t->status === \App\Models\Tenant::ACTIVE ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-center small text-muted">{{ $t->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('root.tenant.switch') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                                        <button type="submit" class="btn btn-sm btn-table-purple me-1" title="Entrar a esta escuela">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('tenants.edit', $t->id) }}" class="btn btn-sm btn-table-green me-1" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenants.activate', $t->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $t->status ? 'btn-table-red' : 'btn-table-green' }} me-1"
                                                title="{{ $t->status ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $t->status ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('tenants.destroy', $t->id) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta escuela permanentemente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-table-red" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-building fa-2x mb-3 d-block opacity-25"></i>
                                    No hay escuelas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
