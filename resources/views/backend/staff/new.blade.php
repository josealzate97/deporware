@extends('backend.layouts.main')

@section('title', 'Nuevo Personal')

@push('styles')
    @vite(['resources/css/modules/staff.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/staff.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'staff.new',
                    'icon' => 'fas fa-briefcase',
                    'label' => 'Nuevo Personal'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Nuevo Personal</h2>
                        <div class="text-muted small fw-bold">Crea un nuevo registro de staff.</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('staff.index') }}" class="btn btn-primary">
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
