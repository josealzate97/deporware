@extends('backend.layouts.main')

@section('title', 'Configuracion - Rivales')

@push('styles')
    @vite(['resources/css/modules/configurations/rivals.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations/rivals.js'])
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
                        <i class="fa-solid fa-shield"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Configuraciones</h2>
                        <div class="text-muted small fw-bold">Gestion de rivales para analisis y registro de partidos</div>
                    </div>
                </div>
                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('configurations.rivals.create') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Nuevo Rival
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 section-card">
            @php
                $activeTab = 'rivals';
            @endphp
            @include('backend.configurations.partials.tabs')

            <div class="section-results-meta px-0">
                <span class="fw-bold">Resultados</span>
                <span class="text-muted">
                    @if($rivals->total() > 0)
                        Mostrando {{ $rivals->firstItem() }}-{{ $rivals->lastItem() }} de {{ $rivals->total() }}
                    @else
                        Mostrando 0-0 de 0
                    @endif
                </span>
            </div>

            <form class="section-toolbar mb-3" method="GET" action="{{ route('configurations.rivals.index') }}">
                <div class="section-search">
                    <i class="fas fa-search"></i>
                    <label class="visually-hidden" for="rivalsSearch">Buscar rival</label>
                    <input type="search" class="form-control form-control-sm" id="rivalsSearch" name="search" value="{{ $search ?? '' }}" placeholder="Buscar rival...">
                </div>
                <label class="visually-hidden" for="rivalsStatusFilter">Filtrar por estado</label>
                <select class="form-select form-select-sm section-filter" id="rivalsStatusFilter" name="status" onchange="this.form.requestSubmit()">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ (string) ($selectedStatus ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm section-filter-btn">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('configurations.rivals.index') }}" class="btn btn-sm section-clear-btn">
                    <i class="fas fa-rotate-left"></i> Limpiar
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-borderless align-middle section-table">
                    <thead>
                        <tr>
                            <th>Rival</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rivals as $rival)
                            <tr>
                                <td class="fw-semibold">{{ $rival->name }}</td>
                                <td>
                                    @if((int) $rival->status === \App\Models\RivalTeam::ACTIVE)
                                        <span class="status-pill status-pill-success">Activo</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('configurations.rivals.edit', $rival->id) }}" class="btn btn-icon btn-icon-edit" title="Editar rival {{ $rival->name }}">
                                        <i class="fas fa-edit mt-1"></i>
                                    </a>
                                    @if((int) $rival->status === \App\Models\RivalTeam::ACTIVE)
                                        <form method="POST" action="{{ route('configurations.rivals.destroy', $rival->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon text-danger" title="Desactivar rival {{ $rival->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('configurations.rivals.activate', $rival->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-icon text-success" title="Activar rival {{ $rival->name }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No hay rivales registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('backend.components.pagination', [
                'paginator' => $rivals,
                'ariaLabel' => 'Paginador de rivales',
            ])
        </div>

    </div>

@endsection
