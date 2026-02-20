@extends('backend.layouts.main')

@section('title', 'Detalle Categorias')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'categorias.show',
                    'icon' => 'fas fa-tags',
                    'label' => 'Detalle Categorias'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-tags"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Detalle Categorias</h2>
                    <div class="text-muted small fw-bold">Revisa el detalle de categorias.</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Vista en construccion.</p>
        </div>

    </div>

@endsection
