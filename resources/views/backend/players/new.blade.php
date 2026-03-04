@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($player = $player ?? null)

@section('title', $isEdit ? 'Editar Jugadores' : 'Nuevo Jugadores')

@push('styles')
    @vite(['resources/css/modules/players.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/players.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => $isEdit ? 'players.index' : 'players.new',
                    'icon' => 'fas fa-people-group',
                    'label' => $isEdit ? 'Editar Jugador' : 'Nuevo Jugador'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fas fa-people-group"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Jugador' : 'Nuevo Jugador' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Modifica los datos personales, deportivos y administrativos del jugador' : 'Registra un nuevo jugador en el sistema y completa su información deportiva y personal' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('players.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <form class="info-form" method="POST" action="{{ $isEdit ? route('players.update', $player?->id) : route('players.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-id-card me-2 text-primary"></i>
                                Datos personales
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Nombre</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $player->name ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Apellidos</label>
                                    <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $player->lastname ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" name="birthdate" value="{{ old('birthdate', $player->birthdate?->format('Y-m-d') ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Nacionalidad</label>
                                    <select class="form-select" name="nacionality" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($nationalityOptions as $key => $label)
                                            <option value="{{ $key }}" {{ (string) old('nacionality', $player->nacionality ?? '') === (string) $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-envelope me-2 text-primary"></i>
                                Contacto
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Correo electrónico</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $player->email ?? '') }}">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" class="form-control mask-phone" name="phone" value="{{ old('phone', $player->phone ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-futbol me-2 text-primary"></i>
                                Perfil deportivo
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Posición</label>
                                    <select class="form-select" name="position" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($positionOptions as $key => $label)
                                            <option value="{{ $key }}" {{ (string) old('position', $player->position ?? '') === (string) $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Dorsal</label>
                                    <input type="number" class="form-control" name="dorsal" min="0" step="1"
                                        value="{{ old('dorsal', $player->dorsal ?? '') }}">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Pierna hábil</label>
                                    <select class="form-select" name="foot" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($footOptions as $key => $label)
                                            <option value="{{ $key }}" {{ (string) old('foot', $player->foot ?? '') === (string) $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Peso (kg)</label>
                                    <input type="number" class="form-control" name="weight" min="0" step="1"
                                        value="{{ old('weight', $player->weight ?? '') }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold d-block">Estado</label>
                                    <div class="form-check form-switch form-switch-lg mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                            {{ old('status', $player->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">Jugador activo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success px-4 fw-bold">
                        <i class="fa fa-save me-2"></i>
                        {{ $isEdit ? 'Guardar Cambios' : 'Guardar Jugador' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
