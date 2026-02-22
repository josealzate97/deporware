@extends('backend.layouts.main')

@section('title', 'Inicio')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb')
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">

                <div class="section-hero-icon">
                    <i class="fas fa-home"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-0">Bienvenido a Deporware</h2>
                    <div class="text-muted small fw-bold">Panel principal listo para usar.</div>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <p class="mb-0 text-muted">Empieza gestionando los usuarios desde el menú lateral.</p>
        </div>

    </div>

@endsection
