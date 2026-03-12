@extends('backend.layouts.main')

@php
    $isEdit = $isEdit ?? false;
@endphp

@section('title', $isEdit ? 'Editar Rival' : 'Nuevo Rival')

@push('styles')
    @vite(['resources/css/modules/configurations/rivals.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations/rivals.js'])
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
                        <i class="fa-solid fa-shield"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Rival' : 'Nuevo Rival' }}</h2>
                        <div class="text-muted small fw-bold">Completa los datos para registrar el rival</div>
                    </div>
                </div>
                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('configurations.rivals.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 section-card">
            @php
                $activeTab = 'rivals';
            @endphp
            @include('backend.configurations.partials.tabs')

            <form class="info-form" method="POST" action="{{ $isEdit ? route('configurations.rivals.update', $rival->id) : route('configurations.rivals.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-shield me-2 text-primary"></i>
                                Datos del rival
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $rival->name ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> {{ $isEdit ? 'Guardar Cambios' : 'Crear Rival' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
