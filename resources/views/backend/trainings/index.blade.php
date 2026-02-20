@extends('backend.layouts.main')

@section('title', 'Entrenamientos')

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

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Entrenamientos</h2>
                    <div class="text-muted small fw-bold">Planifica sesiones de entrenamiento.</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Aqui ira el listado principal.</p>
        </div>

    </div>

@endsection
