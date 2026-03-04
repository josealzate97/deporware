@extends('backend.layouts.main')

@section('title', 'Partidos')

@push('styles')
    @vite(['resources/css/modules/matches.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/matches.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'matches.index',
                    'icon' => 'fa-solid fa-futbol',
                    'label' => 'Partidos'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-futbol"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Partidos</h2>
                        <div class="text-muted small fw-bold">Administra los encuentros programados, resultados y estado de cada partido</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('matches.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Partido
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">
        <div class="card p-0 mt-4 section-card">
            <div class="table-responsive">
                <table class="table table-borderless align-middle section-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Equipo</th>
                            <th>Rival</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matches as $match)
                            <tr data-id="{{ $match->id }}">
                                <td>{{ $match->match_date?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $match->team?->name ?? '-' }}</td>
                                <td>{{ $match->rival?->name ?? '-' }}</td>
                                <td>
                                    <span class="status-pill {{ $match->match_status ? 'status-pill-success' : 'status-pill-muted' }}">
                                        {{ $match->match_status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon text-primary"
                                        @click="openModal('{{ route('matches.show', $match->id) }}?modal=1')"
                                        aria-label="Ver información del partido" title="Ver información">
                                        <i class="fas fa-circle-info"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay partidos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        </div>

        <div class="info-overlay" x-show="open" x-transition.opacity x-cloak @click.self="closeModal">
            <div class="info-panel" x-show="open" x-transition>
                <div class="info-header">
                    <span x-text="title"></span>
                    <button type="button" class="info-close" @click="closeModal">&times;</button>
                </div>
                <div class="info-body" x-html="content"></div>
            </div>
        </div>

        </div>

@endsection
