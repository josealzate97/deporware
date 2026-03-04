@extends('backend.layouts.main')

@section('title', 'Entrenamientos')

@push('styles')
    @vite(['resources/css/modules/trainings.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/trainings.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'trainings.index',
                    'icon' => 'fas fa-dumbbell',
                    'label' => 'Entrenamientos'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Entrenamientos</h2>
                        <div class="text-muted small fw-bold">Consulta y administra las sesiones de entrenamiento programadas y realizadas</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('trainings.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Entrenamiento
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
                            <th>Entrenamiento</th>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainings as $training)
                            <tr data-id="{{ $training->id }}">
                                <td class="fw-bold">{{ $training->name }}</td>
                                <td>{{ $training->team?->name ?? '-' }}</td>
                                <td>
                                    @if($training->status == \App\Models\Training::ACTIVE)
                                        <span class="status-pill status-pill-success">Activo</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon text-primary"
                                        @click="openModal('{{ route('trainings.show', $training->id) }}?modal=1')"
                                        aria-label="Ver información de {{ $training->name }}" title="Ver información">
                                        <i class="fas fa-circle-info"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay entrenamientos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        </div>

        <div class="info-overlay" x-show="open" x-transition.opacity x-cloak @click.self="closeModal">
            <div class="info-panel" :class="open ? 'is-open' : ''" x-show="open" x-transition>
                <div class="info-header">
                    <span x-text="title"></span>
                    <button type="button" class="info-close" @click="closeModal">&times;</button>
                </div>
                <div class="info-body" x-html="content"></div>
            </div>
        </div>

        </div>

@endsection
