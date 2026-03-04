@extends('backend.layouts.main')

@section('title', 'Información del Partido')

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
                    'label' => 'Información del Partido'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fa-solid fa-futbol"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Información del Partido</h2>
                    <div class="text-muted small fw-bold">Visualiza marcador, alineaciones y datos clave del encuentro</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
