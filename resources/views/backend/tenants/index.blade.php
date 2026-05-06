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

            {{-- Meta --}}
            <div class="section-results-meta">
                <span class="fw-bold">Resultados</span>
                <span class="text-muted">
                    @if($tenants->total() > 0)
                        Mostrando {{ $tenants->firstItem() }}-{{ $tenants->lastItem() }} de {{ $tenants->total() }}
                    @else
                        Mostrando 0-0 de 0
                    @endif
                </span>
            </div>

            {{-- Toolbar --}}
            <form method="GET" action="{{ route('tenants.index') }}" class="section-toolbar">
                <div class="section-search">
                    <i class="fas fa-search"></i>
                    <label class="visually-hidden" for="tenantsSearch">Buscar escuela</label>
                    <input type="search" id="tenantsSearch" name="search"
                           class="form-control form-control-sm"
                              placeholder="Buscar por identificador"
                           value="{{ $search }}" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-sm section-filter-btn">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('tenants.index') }}" class="btn btn-sm section-clear-btn">
                    <i class="fas fa-rotate-left"></i> Limpiar
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-borderless align-middle section-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Identificador</th>
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
                                <td class="text-center">
                                    <span class="meta-badge">{{ $t->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="meta-badge">{{ $t->teams_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="meta-badge">{{ $t->players_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill {{ $t->status === \App\Models\Tenant::ACTIVE ? 'status-pill-success' : 'status-pill-muted' }}">
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
                                <td colspan="9" class="py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center gap-2 text-muted">
                                        <i class="fa-solid fa-building fa-2x opacity-25"></i>
                                        @if($search)
                                            <span>No se encontraron escuelas para "<strong>{{ $search }}</strong>".</span>
                                        @else
                                            <span>No hay escuelas registradas.</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('backend.components.pagination', [
                'paginator' => $tenants,
                'ariaLabel' => 'Paginador de escuelas',
            ])

        </div>

    </div>

@endsection
