@extends('backend.layouts.main')

@section('title', 'Información de la Plantilla')

@push('styles')
    @vite(['resources/css/modules/teams.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/teams.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'teams.index',
                    'icon' => 'fa-solid fa-shield',
                    'label' => 'Información de la Plantilla'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fa-solid fa-shield"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Información de la Plantilla</h2>
                    <div class="text-muted small fw-bold">Consulta los jugadores que integran la plantilla y su estado actual</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
