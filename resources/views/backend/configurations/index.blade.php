@extends('backend.layouts.main')

@section('title', 'Configuración')

@push('styles')
    @vite(['resources/css/modules/configurations.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'configurations.index',
                    'icon' => 'fa-solid fa-cog',
                    'label' => 'Configuración'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fa-solid fa-cog"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Configuración</h2>
                    <div class="text-muted small fw-bold">Datos generales de tu escuela deportiva, localización y preferencias</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
