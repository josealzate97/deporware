@extends('backend.layouts.main')

@section('title', 'Editar Sede')

@push('styles')
    @vite(['resources/css/modules/venues.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/venues.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'venues.edit',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'label' => 'Editar Sede'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fa-solid fa-building-circle-check"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Editar Sede</h2>
                    <div class="text-muted small fw-bold">Modifica los datos de la sede deportiva seleccionada</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
