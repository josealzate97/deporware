@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)

@section('title', $isEdit ? 'Editar Partido' : 'Nuevo Partido')

@push('styles')
    @vite(['resources/css/modules/matches.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/matches.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => $isEdit ? 'matches.index' : 'matches.new',
                    'icon' => 'fa-solid fa-futbol',
                    'label' => $isEdit ? 'Editar Partido' : 'Nuevo Partido'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-futbol"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Partido' : 'Nuevo Partido' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Modifica fecha, equipos participantes o datos relevantes del encuentro' : 'Registra un encuentro oficial o amistoso dentro del calendario deportivo' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('matches.index') }}" class="btn btn-primary">
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
