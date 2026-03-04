@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($player = $player ?? null)
@php($activeStep = in_array($step ?? 'player', ['player', 'contacts', 'observations'], true) ? $step : 'player')
@php($contact = $player?->contacts?->first())

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
            <div class="players-wizard-tabs mb-4">
                <a class="players-tab {{ $activeStep === 'player' ? 'is-active' : '' }}"
                   href="{{ $isEdit ? route('players.edit', ['id' => $player->id, 'step' => 'player']) : route('players.new') }}">
                    1. Jugador
                </a>
                @if($isEdit)
                    <a class="players-tab {{ $activeStep === 'contacts' ? 'is-active' : '' }}"
                       href="{{ route('players.edit', ['id' => $player->id, 'step' => 'contacts']) }}">
                        2. Contactos
                    </a>
                    <a class="players-tab {{ $activeStep === 'observations' ? 'is-active' : '' }}"
                       href="{{ route('players.edit', ['id' => $player->id, 'step' => 'observations']) }}">
                        3. Observaciones
                    </a>
                @else
                    <span class="players-tab is-disabled">2. Contactos</span>
                    <span class="players-tab is-disabled">3. Observaciones</span>
                @endif
            </div>

            @if($activeStep === 'player')
                <form class="info-form" method="POST" action="{{ $isEdit ? route('players.update', $player?->id) : route('players.store') }}">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                        <input type="hidden" name="step" value="player">
                    @endif

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="info-section">
                                <div class="info-section-title">
                                    <i class="fa-solid fa-id-card me-2 text-primary"></i>
                                    Datos personales
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Nombre</label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $player->name ?? '') }}" required>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Apellidos</label>
                                        <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $player->lastname ?? '') }}" required>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Identificación (NIT)</label>
                                        <input type="text" class="form-control" name="nit" value="{{ old('nit', $player->nit ?? '') }}" required>
                                    </div>
                                    <div class="col-12 col-lg-3">
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
                                        <select class="form-select" name="position">
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
                            {{ $isEdit ? 'Guardar y continuar' : 'Guardar jugador' }}
                        </button>
                    </div>
                </form>
            @endif

            @if($activeStep === 'contacts')
                @if(!$isEdit)
                    <div class="text-muted">Primero guarda los datos del jugador para habilitar contactos.</div>
                @else
                    <form class="info-form" method="POST" action="{{ route('players.update', $player->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="step" value="contacts">

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="info-section">
                                    <div class="info-section-title">
                                        <i class="fa-solid fa-people-roof me-2 text-primary"></i>
                                        Contacto del jugador
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Nombre</label>
                                            <input type="text" class="form-control" name="contact_name"
                                                value="{{ old('contact_name', $contact->name ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Apellidos</label>
                                            <input type="text" class="form-control" name="contact_lastname"
                                                value="{{ old('contact_lastname', $contact->lastname ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Correo electrónico</label>
                                            <input type="email" class="form-control" name="contact_email"
                                                value="{{ old('contact_email', $contact->email ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Teléfono</label>
                                            <input type="text" class="form-control mask-phone" name="contact_phone"
                                                value="{{ old('contact_phone', $contact->phone ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Dirección</label>
                                            <input type="text" class="form-control" name="contact_address"
                                                value="{{ old('contact_address', $contact->address ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Ciudad</label>
                                            <input type="text" class="form-control" name="contact_city"
                                                value="{{ old('contact_city', $contact->city ?? '') }}" required>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold d-block">Estado</label>
                                            <div class="form-check form-switch form-switch-lg mt-2">
                                                <input class="form-check-input" type="checkbox" name="contact_status" value="1"
                                                    {{ old('contact_status', $contact->status ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold">Contacto activo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="fa fa-save me-2"></i>
                                Guardar contacto
                            </button>
                        </div>
                    </form>
                @endif
            @endif

            @if($activeStep === 'observations')
                @if(!$isEdit)
                    <div class="text-muted">Primero guarda los datos del jugador para habilitar observaciones.</div>
                @else
                    <div class="info-section">
                        <div class="info-section-title">
                            <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>
                            Observaciones
                        </div>

                        @if($player->observations->isEmpty())
                            <div class="text-muted">Sin observaciones registradas.</div>
                        @else
                            <div class="row g-2 mt-2">
                                @foreach($player->observations as $observation)
                                    <div class="col-12 col-lg-6">
                                        <div class="team-info-item">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $observationTypes[$observation->type] ?? 'Sin tipo' }}</div>
                                                <div class="text-muted small">{{ $observation->notes ?? '-' }}</div>
                                                <div class="text-muted small">
                                                    {{ $observation->user?->name ?? 'Usuario' }} · {{ $observation->created_at?->format('Y-m-d') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

    </div>

@endsection
