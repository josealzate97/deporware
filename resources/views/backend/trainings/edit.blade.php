@extends('backend.layouts.main')

@section('title', 'Editar Entrenamiento')

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
                    'route' => 'trainings.edit',
                    'icon' => 'fas fa-dumbbell',
                    'label' => 'Editar Entrenamiento'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Editar Entrenamiento</h2>
                    <div class="text-muted small fw-bold">Actualiza la información de la sesión y ajusta horarios, participantes u objetivos</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
