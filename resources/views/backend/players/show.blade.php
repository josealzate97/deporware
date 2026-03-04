@extends('backend.layouts.main')

@section('title', 'Detalle Jugadores')

@push('styles')
    @vite(['resources/css/modules/players.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/players.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'players.index',
                    'icon' => 'fas fa-people-group',
                    'label' => 'Información del Jugador'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-people-group"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Información del Jugador</h2>
                    <div class="text-muted small fw-bold">Visualiza el historial, estadísticas y detalles generales del jugador</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
