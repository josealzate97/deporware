@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)

@section('title', $isEdit ? 'Editar Sede' : 'Nueva Sede')

@push('styles')
    @vite(['resources/css/modules/venues.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/venues.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => $isEdit ? 'venues.index' : 'venues.new',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'label' => $isEdit ? 'Editar Sede' : 'Nueva Sede'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-building-circle-check"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Sede' : 'Nueva Sede' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Modifica los datos de la sede deportiva seleccionada' : 'Registra una nueva sede deportiva con su ubicación y datos de contacto' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('venues.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <form class="info-form" method="POST" action="{{ $isEdit ? route('venues.update', $venue->id) : route('venues.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">Datos de la sede</div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Nombre</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $venue->name) }}" required>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Dirección</label>
                                    <input type="text" class="form-control" name="address" value="{{ old('address', $venue->address) }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Ciudad</label>
                                    <input type="text" class="form-control" name="city" value="{{ old('city', $venue->city) }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold d-block">Estado</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                            {{ old('status', $venue->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label">Sede activa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> {{ $isEdit ? 'Guardar Cambios' : 'Crear Sede' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
