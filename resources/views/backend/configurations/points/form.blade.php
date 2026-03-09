@extends('backend.layouts.main')

@php
    $isEdit = $isEdit ?? false;
    $isAttack = ($pointType ?? 'attack') === 'attack';
@endphp

@section('title', $isEdit ? 'Editar Punto' : 'Nuevo Punto')

@push('styles')
    @vite(['resources/css/modules/configurations/points.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations/points.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'configurations.index',
                    'icon' => 'fa-solid fa-cog',
                    'label' => 'Configuracion'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="{{ $isAttack ? 'fa-solid fa-bolt' : 'fa-solid fa-shield-halved' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Punto' : 'Nuevo Punto' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isAttack ? 'Gestiona puntos fuertes de ataque' : 'Gestiona puntos debiles de defensa' }}
                        </div>
                    </div>
                </div>
                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('configurations.points.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 section-card">
            @php
                $activeTab = 'points';
            @endphp
            @include('backend.configurations.partials.tabs')

            @php
                $action = $isAttack
                    ? ($isEdit ? route('configurations.points.attack.update', $point->id) : route('configurations.points.attack.store'))
                    : ($isEdit ? route('configurations.points.defensive.update', $point->id) : route('configurations.points.defensive.store'));
            @endphp

            <form class="info-form" method="POST" action="{{ $action }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="{{ $isAttack ? 'fa-solid fa-bolt' : 'fa-solid fa-shield-halved' }} me-2 text-primary"></i>
                                {{ $isAttack ? 'Punto fuerte (ataque)' : 'Punto debil (defensa)' }}
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $point->name ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> {{ $isEdit ? 'Guardar Cambios' : 'Crear Punto' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
