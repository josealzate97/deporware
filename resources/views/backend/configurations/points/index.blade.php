@extends('backend.layouts.main')

@section('title', 'Configuracion - Puntos')

@push('styles')
    @vite(['resources/css/modules/configurations/points.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations/points.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'configurations.index',
                    'icon' => 'fa-solid fa-cog',
                    'label' => 'Configuracion'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Configuraciones</h2>
                        <div class="text-muted small fw-bold">Administra catalogos de puntos fuertes y debiles para evaluaciones</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 section-card">
            @php
                $activeTab = 'points';
            @endphp
            @include('backend.configurations.partials.tabs')

            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="info-section h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="info-section-title mb-0">
                                <i class="fa-solid fa-bolt me-2 text-primary"></i>
                                Puntos fuertes (ataque)
                            </div>
                            <a href="{{ route('configurations.points.attack.create') }}" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-plus-circle me-1"></i> Nuevo
                            </a>
                        </div>

                        <div class="section-results-meta px-0 pt-0">
                            <span class="fw-bold">Resultados</span>
                            <span class="text-muted">
                                @if($attackPoints->total() > 0)
                                    Mostrando {{ $attackPoints->firstItem() }}-{{ $attackPoints->lastItem() }} de {{ $attackPoints->total() }}
                                @else
                                    Mostrando 0-0 de 0
                                @endif
                            </span>
                        </div>

                        <form class="section-toolbar mb-3" method="GET" action="{{ route('configurations.points.index') }}">
                            <input type="hidden" name="defensive_search" value="{{ $defensiveSearch ?? '' }}">
                            <input type="hidden" name="defensive_status" value="{{ $defensiveStatus ?? '' }}">
                            <div class="section-search">
                                <i class="fas fa-search"></i>
                                <label class="visually-hidden" for="attackPointsSearch">Buscar punto de ataque</label>
                                <input type="search" class="form-control form-control-sm" id="attackPointsSearch" name="attack_search" value="{{ $attackSearch ?? '' }}" placeholder="Buscar punto de ataque...">
                            </div>
                            <label class="visually-hidden" for="attackPointsStatus">Filtrar por estado</label>
                            <select class="form-select form-select-sm section-filter" id="attackPointsStatus" name="attack_status" onchange="this.form.requestSubmit()">
                                <option value="">Todos</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ (string) ($attackStatus ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm section-filter-btn">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('configurations.points.index', ['defensive_search' => $defensiveSearch ?? '', 'defensive_status' => $defensiveStatus ?? '']) }}" class="btn btn-sm section-clear-btn">
                                <i class="fas fa-rotate-left"></i> Limpiar
                            </a>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle section-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Punto</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attackPoints as $point)
                                        <tr>
                                            <td>{{ $point->name }}</td>
                                            <td>
                                                @if((int) $point->status === \App\Models\AttackPoint::ACTIVE)
                                                    <span class="status-pill status-pill-success">Activo</span>
                                                @else
                                                    <span class="status-pill status-pill-muted">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="config-points-actions">
                                                    <a href="{{ route('configurations.points.attack.edit', $point->id) }}" class="btn btn-icon btn-icon-edit config-edit-btn config-point-action-btn" title="Editar punto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if((int) $point->status === \App\Models\AttackPoint::ACTIVE)
                                                        <form method="POST" action="{{ route('configurations.points.attack.destroy', $point->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-icon config-point-action-btn config-point-action-danger" title="Desactivar punto">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('configurations.points.attack.activate', $point->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-icon config-point-action-btn config-point-action-success" title="Activar punto">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Sin puntos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @include('backend.components.pagination', [
                            'paginator' => $attackPoints,
                            'ariaLabel' => 'Paginador de puntos de ataque',
                            'wrapperClass' => 'section-pagination mt-3',
                        ])
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="info-section h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="info-section-title mb-0">
                                <i class="fa-solid fa-shield-halved me-2 text-primary"></i>
                                Puntos debiles (defensa)
                            </div>
                            <a href="{{ route('configurations.points.defensive.create') }}" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-plus-circle me-1"></i> Nuevo
                            </a>
                        </div>

                        <div class="section-results-meta px-0 pt-0">
                            <span class="fw-bold">Resultados</span>
                            <span class="text-muted">
                                @if($defensivePoints->total() > 0)
                                    Mostrando {{ $defensivePoints->firstItem() }}-{{ $defensivePoints->lastItem() }} de {{ $defensivePoints->total() }}
                                @else
                                    Mostrando 0-0 de 0
                                @endif
                            </span>
                        </div>

                        <form class="section-toolbar mb-3" method="GET" action="{{ route('configurations.points.index') }}">
                            <input type="hidden" name="attack_search" value="{{ $attackSearch ?? '' }}">
                            <input type="hidden" name="attack_status" value="{{ $attackStatus ?? '' }}">
                            <div class="section-search">
                                <i class="fas fa-search"></i>
                                <label class="visually-hidden" for="defensivePointsSearch">Buscar punto defensivo</label>
                                <input type="search" class="form-control form-control-sm" id="defensivePointsSearch" name="defensive_search" value="{{ $defensiveSearch ?? '' }}" placeholder="Buscar punto defensivo...">
                            </div>
                            <label class="visually-hidden" for="defensivePointsStatus">Filtrar por estado</label>
                            <select class="form-select form-select-sm section-filter" id="defensivePointsStatus" name="defensive_status" onchange="this.form.requestSubmit()">
                                <option value="">Todos</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ (string) ($defensiveStatus ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm section-filter-btn">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('configurations.points.index', ['attack_search' => $attackSearch ?? '', 'attack_status' => $attackStatus ?? '']) }}" class="btn btn-sm section-clear-btn">
                                <i class="fas fa-rotate-left"></i> Limpiar
                            </a>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle section-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Punto</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($defensivePoints as $point)
                                        <tr>
                                            <td>{{ $point->name }}</td>
                                            <td>
                                                @if((int) $point->status === \App\Models\DefensivePoint::ACTIVE)
                                                    <span class="status-pill status-pill-success">Activo</span>
                                                @else
                                                    <span class="status-pill status-pill-muted">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="config-points-actions">
                                                    <a href="{{ route('configurations.points.defensive.edit', $point->id) }}" class="btn btn-icon btn-icon-edit config-edit-btn config-point-action-btn" title="Editar punto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if((int) $point->status === \App\Models\DefensivePoint::ACTIVE)
                                                        <form method="POST" action="{{ route('configurations.points.defensive.destroy', $point->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-icon config-point-action-btn config-point-action-danger" title="Desactivar punto">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('configurations.points.defensive.activate', $point->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-icon config-point-action-btn config-point-action-success" title="Activar punto">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Sin puntos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @include('backend.components.pagination', [
                            'paginator' => $defensivePoints,
                            'ariaLabel' => 'Paginador de puntos defensivos',
                            'wrapperClass' => 'section-pagination mt-3',
                        ])
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
