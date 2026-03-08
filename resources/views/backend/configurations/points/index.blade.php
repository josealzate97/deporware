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
            @php($activeTab = 'points')
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

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle section-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Punto</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attackPoints as $point)
                                        <tr>
                                            <td>{{ $point->name }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('configurations.points.attack.edit', $point->id) }}" class="btn btn-icon btn-icon-edit" title="Editar punto">
                                                    <i class="fas fa-edit mt-1"></i>
                                                </a>
                                                <form method="POST" action="{{ route('configurations.points.attack.destroy', $point->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon text-danger" title="Eliminar punto">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">Sin puntos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle section-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Punto</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($defensivePoints as $point)
                                        <tr>
                                            <td>{{ $point->name }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('configurations.points.defensive.edit', $point->id) }}" class="btn btn-icon btn-icon-edit" title="Editar punto">
                                                    <i class="fas fa-edit mt-1"></i>
                                                </a>
                                                <form method="POST" action="{{ route('configurations.points.defensive.destroy', $point->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon text-danger" title="Eliminar punto">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">Sin puntos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
