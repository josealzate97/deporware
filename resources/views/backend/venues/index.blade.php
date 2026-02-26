@extends('backend.layouts.main')

@section('title', 'Sedes')

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
                    'route' => 'venues.index',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'label' => 'Sedes'
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
                        <h2 class="fw-bold mb-0">Sedes Deportivas</h2>
                        <div class="text-muted small fw-bold">Gestiona las sedes donde opera tu escuela deportiva</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('venues.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Sede
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Aqui ira el listado principal.</p>
        </div>

    </div>

@endsection
