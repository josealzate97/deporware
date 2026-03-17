@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($player = $player ?? null)
@php($activeStep = in_array($step ?? 'player', ['player', 'contacts', 'observations'], true) ? $step : 'player')
@php($activeContactId = request()->query('contact_id'))
@php($contact = $activeContactId ? $player?->contacts?->firstWhere('id', $activeContactId) : null)
@php($activeObservationId = request()->query('observation_id'))
@php($observation = $activeObservationId ? $player?->observations?->firstWhere('id', $activeObservationId) : null)
@php($selectedPositions = \App\Models\Player::normalizePositions(old('positions', $player?->resolved_positions ?? [])))

@section('title', $isEdit ? 'Editar Jugadores' : 'Nuevo Jugadores')

@push('styles')
    @vite(['resources/css/modules/players.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/players.js'])
@endpush

@section('content')

    <div class="container-fluid p-4" x-data="playersPage()">

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
                        3. Ficha Valorativa
                    </a>
                @else
                    <span class="players-tab is-disabled">2. Contactos</span>
                    <span class="players-tab is-disabled">3. Ficha Valorativa</span>
                @endif
            </div>

            @if($activeStep === 'player')
                <form class="info-form" data-validate="app" novalidate method="POST" action="{{ $isEdit ? route('players.update', $player?->id) : route('players.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                        <input type="hidden" name="step" value="player">
                    @endif
                    @include('backend.components.form-errors')

                    <div class="row g-4">

                        <div class="col-12">

                            <div class="info-section">
                                <div class="info-section-title">
                                    <i class="fa-solid fa-id-card me-2 text-primary"></i>
                                    Datos personales
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-12 col-lg-7">
                                        <div class="row g-3">
                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="name" value="{{ old('name', $player->name ?? '') }}" required>
                                            </div>

                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $player->lastname ?? '') }}" required>
                                            </div>

                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Identificación (NIT) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nit" value="{{ old('nit', $player->nit ?? '') }}" required>
                                            </div>

                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="birthdate" value="{{ old('birthdate', $player->birthdate?->format('Y-m-d') ?? '') }}" required>
                                            </div>

                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Nacionalidad <span class="text-danger">*</span></label>
                                                <select class="form-select" name="nacionality" required>
                                                    <option value="">Selecciona...</option>
                                                    @foreach($nationalityOptions as $key => $label)
                                                        <option value="{{ $key }}" {{ (string) old('nacionality', $player->nacionality ?? '') === (string) $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-lg-6">
                                                <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control mask-phone" name="phone" value="{{ old('phone', $player->phone ?? '') }}" required>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" value="{{ old('email', $player->email ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    @php($playerPhotoUrl = $player?->photo ? \Illuminate\Support\Facades\Storage::url($player->photo) : '')
                                    <div class="col-12 col-lg-5">
                                        <div class="player-photo-panel">
                                            <div class="player-photo-panel-header">
                                                <div>
                                                    <div class="fw-semibold">Foto del jugador</div>
                                                    <span class="player-badge-blue">JPG o PNG · Máx 5MB</span>
                                                </div>
                                                <div class="player-photo-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-photo-action="upload">
                                                        <i class="fa-solid fa-upload me-1"></i> Subir
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-photo-action="remove">
                                                        <i class="fa-solid fa-trash me-1"></i> Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="file" class="form-control d-none" name="photo" id="player_photo" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" name="remove_photo" id="remove_photo" value="0">
                                            <div class="player-photo-preview mt-3" data-photo-preview data-photo-url="{{ $playerPhotoUrl }}">
                                                <div class="player-photo-frame">
                                                    <img class="player-photo-img" data-photo-img data-open-lightbox="true" alt="Foto del jugador" title="Click para ampliar">
                                                    <div class="player-photo-empty" data-photo-empty>No valido por el momento</div>
                                                </div>
                                                <div class="player-photo-hint">Vista previa de la foto.</div>
                                            </div>
                                        </div>
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
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label fw-semibold">Posición <span class="text-danger">*</span></label>
                                        <div class="player-positions-field player-positions-field--featured" data-player-positions>
                                            <div class="row g-3 align-items-stretch">
                                                <div class="col-12 col-md-6">
                                                    <div class="player-positions-controls player-positions-controls--stack">
                                                        <select class="form-select" data-player-position-select>
                                                            <option value="">Selecciona una posición...</option>
                                                            @foreach($positionOptions as $key => $label)
                                                                <option value="{{ $key }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-success player-position-add-btn" data-player-position-add aria-label="Agregar posición" title="Agregar posición">
                                                            <i class="fa-solid fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="player-positions-selected player-positions-selected--panel" data-player-positions-selected>
                                                        <span class="player-positions-empty {{ count($selectedPositions) ? 'd-none' : '' }}" data-player-positions-empty>Agrega una o varias posiciones.</span>
                                                        @foreach($selectedPositions as $positionId)
                                                            <span class="player-position-chip" data-position-chip="{{ $positionId }}">
                                                                {{ $positionOptions[$positionId] ?? 'Posición' }}
                                                                <button type="button" class="player-position-chip-remove" data-remove-position="{{ $positionId }}" aria-label="Quitar posición">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                                <input type="hidden" name="positions[]" value="{{ $positionId }}">
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <template data-player-position-template>
                                                <span class="player-position-chip" data-position-chip="__VALUE__">
                                                    __LABEL__
                                                    <button type="button" class="player-position-chip-remove" data-remove-position="__VALUE__" aria-label="Quitar posición">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                    <input type="hidden" name="positions[]" value="__VALUE__">
                                                </span>
                                            </template>
                                        </div>

                                        @error('positions')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('positions.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Puedes asignar varias posiciones sin repetirlas.</div>
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <div class="player-sport-side-panel">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label fw-semibold">Equipo <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="team_id" required>
                                                        <option value="">Selecciona...</option>
                                                        @foreach($teamOptions as $teamId => $teamName)
                                                            <option value="{{ $teamId }}" {{ (string) old('team_id', $selectedTeamId ?? '') === (string) $teamId ? 'selected' : '' }}>
                                                                {{ $teamName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <label class="form-label fw-semibold">Dorsal</label>
                                                    <input type="number" class="form-control" name="dorsal" min="0" step="1"
                                                        value="{{ old('dorsal', $player->dorsal ?? '') }}">
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <label class="form-label fw-semibold">Pierna hábil <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="foot" required>
                                                        <option value="">Selecciona...</option>
                                                        @foreach($footOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ (string) old('foot', $player->foot ?? '') === (string) $key ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <label class="form-label fw-semibold">Peso (kg) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="weight" min="0" step="1"
                                                        value="{{ old('weight', $player->weight ?? '') }}" required>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-semibold d-block">Estado</label>
                                                    <div class="form-check form-switch form-switch-lg mt-2 player-status-check">
                                                        <input class="form-check-input player-status-switch" id="player-status" type="checkbox" name="status" value="1"
                                                            {{ old('status', $player->status ?? true) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold player-status-label mt-1" for="player-status">Jugador activo</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        @if(!$isEdit)

                            <div class="col-12">

                                <div class="info-section">
                                    
                                    <div class="info-section-title">
                                        <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>
                                        Observación inicial (opcional)
                                    </div>

                                    <div class="row g-3 mt-1">

                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-semibold">Tipo</label>
                                            <select class="form-select" name="initial_observation_type">
                                                <option value="">Selecciona...</option>
                                                @foreach($observationTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ (string) old('initial_observation_type') === (string) $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Notas</label>
                                            <textarea class="form-control" name="initial_observation_notes" rows="5" placeholder="Escribe una observación inicial...">{{ old('initial_observation_notes') }}</textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>

                    <div class="mt-4 text-end">
                        @if($isEdit)
                            <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'player']) }}" class="btn player-btn-cancel px-4 fw-bold me-2">
                                <i class="fa-solid fa-times me-2"></i>
                                Cancelar
                            </a>
                        @endif
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
                    @php($relationshipOptions = \App\Models\PlayerContact::relationshipOptions())
                    <div class="mb-3">

                        <div class="fw-semibold mb-2">Contactos registrados</div>
                        
                            @if($player->contacts->isEmpty())
                                <div class="text-muted player-empty-state"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>Sin contactos registrados.</div>
                            @else

                                <div class="row g-2">
                                    @foreach($player->contacts as $listedContact)
                                        <div class="col-12 col-lg-4">
                                            <div class="player-contact-card h-100">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <div class="fw-semibold">{{ $listedContact->name }} {{ $listedContact->lastname }}</div>
                                                        <span class="player-badge-blue">{{ \App\Models\PlayerContact::relationshipOptions()[$listedContact->relationship] ?? '-' }}</span>
                                                    </div>
                                                    <div class="text-muted small">{{ $listedContact->email ?? '-' }}</div>
                                                    <div class="text-muted small">
                                                        <span class="player-badge-blue">{{ $listedContact->phone ?? '-' }}</span>
                                                        <span class="player-badge-blue">{{ $listedContact->city ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="contact-actions">
                                                    <a class="btn btn-icon btn-icon-edit"
                                                    href="{{ route('players.edit', ['id' => $player->id, 'step' => 'contacts', 'contact_id' => $listedContact->id]) }}"
                                                    title="Editar contacto">
                                                        <i class="fas fa-edit mt-1"></i>
                                                    </a>
                                                    @if($listedContact->status == \App\Models\PlayerContact::ACTIVE)
                                                        <button type="button" class="btn btn-icon text-danger" title="Desactivar contacto"
                                                            @click="openConfirm({
                                                                title: 'Desactivar contacto',
                                                                message: '¿Deseas desactivar este contacto?',
                                                                action: '{{ route('players.contacts.destroy', ['id' => $player->id, 'contactId' => $listedContact->id]) }}',
                                                                method: 'DELETE',
                                                                successMessage: 'Contacto desactivado.'
                                                            })">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-icon text-success" title="Activar contacto"
                                                            @click="openConfirm({
                                                                title: 'Activar contacto',
                                                                message: '¿Deseas activar este contacto?',
                                                                action: '{{ route('players.contacts.activate', ['id' => $player->id, 'contactId' => $listedContact->id]) }}',
                                                                method: 'POST',
                                                                successMessage: 'Contacto activado.'
                                                            })">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 d-flex align-items-center justify-content-between">
                                    <div class="text-muted small">Puedes continuar al paso de ficha valorativa.</div>
                                    <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'observations']) }}" class="btn btn-outline-primary btn-sm">
                                        Ir a ficha valorativa
                                    </a>
                                </div>

                            @endif


                    </div>

                    @php($relationshipOptions = \App\Models\PlayerContact::relationshipOptions())
                    <form class="info-form" data-validate="app" novalidate method="POST" action="{{ route('players.update', $player->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="step" value="contacts">
                        <input type="hidden" name="contact_id" value="{{ $contact?->id }}">
                        @include('backend.components.form-errors')

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
                                            <label class="form-label fw-semibold">Parentesco</label>
                                            <select class="form-select" name="contact_relationship" required>
                                                <option value="">Selecciona...</option>
                                                @foreach($relationshipOptions as $key => $label)
                                                    <option value="{{ $key }}" {{ (string) old('contact_relationship', $contact->relationship ?? '') === (string) $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
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
                                        <div class="form-check form-switch form-switch-lg mt-2 player-status-check">
                                            <input class="form-check-input player-status-switch" id="contact-status" type="checkbox" name="contact_status" value="1"
                                                {{ old('contact_status', $contact->status ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold player-status-label" for="contact-status">Contacto activo</label>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'contacts']) }}" class="btn player-btn-cancel px-4 fw-bold me-2">
                                <i class="fa-solid fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="fa fa-save me-2"></i>
                                {{ $contact ? 'Actualizar contacto' : 'Guardar contacto' }}
                            </button>
                        </div>
                    </form>
                @endif
            @endif

            @if($activeStep === 'observations')
                @if(!$isEdit)
                    <div class="text-muted">Primero guarda los datos del jugador para habilitar la ficha valorativa.</div>
                @else
                    <div class="info-section mb-3">
                        <div class="info-section-title">
                            <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>
                            Ficha valorativa registrada
                        </div>

                        @if($player->observations->isEmpty())
                            <div class="text-muted player-empty-state"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>Sin ficha valorativa registrada.</div>
                        @else
                            <div class="row g-2 mt-2">
                                @foreach($player->observations as $listedObservation)
                                    <div class="col-12 col-lg-4">
                                        <div class="team-info-item h-100">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $observationTypes[$listedObservation->type] ?? 'Sin tipo' }}</div>
                                                <div class="text-muted small">{{ $listedObservation->notes ?? '-' }}</div>
                                                <div class="text-muted small">
                                                    <span class="fw-semibold"><i class="fa-solid fa-user me-1"></i>{{ $listedObservation->author?->name ?? 'Usuario' }}</span>
                                                    <span class="player-badge-blue">{{ $listedObservation->created_at?->format('Y-m-d') ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <a class="btn btn-icon btn-icon-edit"
                                                   href="{{ route('players.edit', ['id' => $player->id, 'step' => 'observations', 'observation_id' => $listedObservation->id]) }}"
                                                   title="Editar observación">
                                                    <i class="fas fa-edit mt-1"></i>
                                                </a>
                                                @if($listedObservation->status == \App\Models\PlayerObservation::ACTIVE)
                                                    <button type="button" class="btn btn-icon text-danger" title="Desactivar observación"
                                                        @click="openConfirm({
                                                            title: 'Desactivar observación',
                                                            message: '¿Deseas desactivar esta observación?',
                                                            action: '{{ route('players.observations.destroy', ['id' => $player->id, 'observationId' => $listedObservation->id]) }}',
                                                            method: 'DELETE',
                                                            successMessage: 'Observación desactivada.'
                                                        })">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-icon text-success" title="Activar observación"
                                                        @click="openConfirm({
                                                            title: 'Activar observación',
                                                            message: '¿Deseas activar esta observación?',
                                                            action: '{{ route('players.observations.activate', ['id' => $player->id, 'observationId' => $listedObservation->id]) }}',
                                                            method: 'POST',
                                                            successMessage: 'Observación activada.'
                                                        })">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <form class="info-form" data-validate="app" novalidate method="POST" action="{{ route('players.update', $player->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="step" value="observations">
                        <input type="hidden" name="observation_id" value="{{ $observation?->id }}">
                        @include('backend.components.form-errors')

                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>
                                {{ $observation ? 'Editar observación' : 'Nueva observación' }}
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Tipo</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($observationTypes as $key => $label)
                                            <option value="{{ $key }}" {{ (string) old('type', $observation->type ?? '') === (string) $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold d-block">Estado</label>
                                    <div class="form-check form-switch form-switch-lg mt-2 player-status-check">
                                        <input class="form-check-input player-status-switch" id="observation-status" type="checkbox" name="status" value="1"
                                            {{ old('status', $observation->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold player-status-label" for="observation-status">Observación activa</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control" name="notes" rows="5" placeholder="Escribe la observación...">{{ old('notes', $observation->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'observations']) }}" class="btn player-btn-cancel px-4 fw-bold me-2">
                                <i class="fa-solid fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="fa fa-save me-2"></i>
                                {{ $observation ? 'Actualizar observación' : 'Guardar observación' }}
                            </button>
                        </div>
                    </form>
                @endif
            @endif
        </div>

    </div>

    <div class="info-overlay" x-show="confirmOpen" x-transition.opacity x-cloak @click.self="closeConfirm">
        <div class="info-panel" :class="confirmOpen ? 'is-open' : ''" x-show="confirmOpen" x-transition>
            <div class="info-header">
                <span x-text="confirmTitle"></span>
                <button type="button" class="info-close" @click="closeConfirm">&times;</button>
            </div>
            <div class="info-body">
                <div class="info-section">
                    <div class="info-section-title">
                        <i class="fa-solid fa-circle-question me-2 text-primary"></i>
                        Confirmación
                    </div>
                    <p class="mb-0" x-text="confirmMessage"></p>
                </div>
                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold me-2" @click="closeConfirm">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4 fw-bold" @click="runConfirm">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
