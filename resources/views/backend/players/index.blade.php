@extends('backend.layouts.main')

@section('title', 'Jugadores')

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
                    'icon' => 'fa-solid fa-people-group',
                    'label' => 'Jugadores'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-people-group"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Jugadores</h2>
                        <div class="text-muted small fw-bold">Administra los jugadores del equipo.</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('players.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus me-2"></i> Crear Jugador
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Aqui ira el listado principal.</p>
        </div>

    </div>

@endsection
