@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)

@section('title', $isEdit ? 'Editar Sede' : 'Nueva Sede')

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
                    'route' => $isEdit ? 'venues.index' : 'venues.new',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'label' => $isEdit ? 'Editar Sede' : 'Nueva Sede'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-building-circle-check"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Sede' : 'Nueva Sede' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Modifica los datos de la sede deportiva seleccionada' : 'Registra una nueva sede deportiva con su ubicación y datos de contacto' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('venues.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
