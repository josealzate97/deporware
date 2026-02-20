@extends('backend.layouts.main')

@section('title', 'Categorias')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'categories.index',
                    'icon' => 'fas fa-tags',
                    'label' => 'Categorias'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-tags"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Categorias</h2>
                    <div class="text-muted small fw-bold">Organiza categorias y niveles.</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Aqui ira el listado principal.</p>
        </div>

    </div>

@endsection
