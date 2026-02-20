@extends('backend.layouts.main')

@section('title', 'Plantillas')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'teams.index',
                    'icon' => 'fas fa-layer-group',
                    'label' => 'Plantillas'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Plantillas</h2>
                    <div class="text-muted small fw-bold">Configura plantillas y alineaciones.</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Aqui ira el listado principal.</p>
        </div>

    </div>

@endsection
