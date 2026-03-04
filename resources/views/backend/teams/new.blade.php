@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($team = $team ?? null)

@section('title', $isEdit ? 'Editar Plantillas' : 'Nuevo Plantillas')

@push('styles')
    @vite(['resources/css/modules/teams.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/teams.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => $isEdit ? 'teams.index' : 'teams.new',
                    'icon' => 'fa-solid fa-shield',
                    'label' => $isEdit ? 'Editar Plantillas' : 'Nuevo Plantillas'
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
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Plantillas' : 'Nueva Plantilla' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Ajusta la plantilla según cambios de temporada, transferencias o decisiones técnicas' : 'Configura una nueva plantilla asignando jugadores y estructura deportiva' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('teams.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <form class="info-form" method="POST" action="{{ $isEdit ? route('teams.update', $team?->id) : route('teams.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-shield me-2 text-primary"></i>
                                Datos de la plantilla
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Nombre</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $team->name ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Año</label>
                                    <input type="text" class="form-control" name="year" maxlength="4" value="{{ old('year', $team->year ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Temporada</label>
                                    <input type="text" class="form-control" name="season" maxlength="20" value="{{ old('season', $team->season ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Tipo</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Selecciona...</option>
                                        <option value="1" {{ (string) old('type', $team->type ?? '') === '1' ? 'selected' : '' }}>Competitivo</option>
                                        <option value="2" {{ (string) old('type', $team->type ?? '') === '2' ? 'selected' : '' }}>Formativo</option>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold d-block">Estado</label>
                                    <div class="form-check form-switch form-switch-lg mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                            {{ old('status', $team->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label">Plantilla activa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> {{ $isEdit ? 'Guardar Cambios' : 'Crear Plantilla' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
