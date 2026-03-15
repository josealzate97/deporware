@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($team = $team ?? null)
@php($seasonSuggestion = now()->year . '-' . (now()->year + 1))
@php($selectedPlayerIds = (array) old('players', $selectedPlayerIds ?? []))
@php($positionOptions = \App\Models\Player::positionOptions())

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
            @php($selectedCoachPrimary = old('coach_primary', $coachPrimaryId ?? ''))
            @php($selectedCoachSecondary = old('coach_secondary', $coachSecondaryId ?? ''))

            <form class="info-form" data-validate="app" novalidate method="POST" action="{{ $isEdit ? route('teams.update', $team?->id) : route('teams.store') }}"
                x-data="{ coachA: '', coachB: '' }"
                x-init="coachA = @js($selectedCoachPrimary); coachB = @js($selectedCoachSecondary)"
                x-effect="if (coachA && coachB && coachA === coachB) { coachB = '' }">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif
                @include('backend.components.form-errors')

                <div class="row g-4 teams-form-layout">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-shield me-2 text-primary"></i>
                                Datos de la plantilla
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $team->name ?? '') }}" required>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control"
                                        name="year"
                                        maxlength="9"
                                        inputmode="numeric"
                                        placeholder="2016-2018"
                                        value="{{ old('year', $team->year ?? '') }}"
                                        required>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="form-label fw-semibold">Temporada <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control"
                                        name="season"
                                        maxlength="9"
                                        inputmode="numeric"
                                        placeholder="2026-2027"
                                        value="{{ old('season', $team->season ?? $seasonSuggestion) }}"
                                        required>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Selecciona...</option>
                                        <option value="1" {{ (string) old('type', $team->type ?? '') === '1' ? 'selected' : '' }}>Competitivo</option>
                                        <option value="2" {{ (string) old('type', $team->type ?? '') === '2' ? 'selected' : '' }}>Formativo</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold d-block">Estado</label>
                                    <div class="form-check form-switch form-switch-lg mt-2 team-status-switch">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                            {{ old('status', $team->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">Plantilla activa</label>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4 d-none d-lg-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-location-dot me-2 text-primary"></i>
                                Sedes asociadas
                            </div>

                            <div class="row g-3 mt-1">
                                @forelse($venues as $venue)
                                    <div class="col-12 col-sm-6">
                                        <div class="form-check form-switch form-switch-lg team-venue-switch">
                                            <input class="form-check-input" type="checkbox" name="venues[]"
                                                value="{{ $venue->id }}"
                                                {{ in_array($venue->id, $teamVenueIds ?? [], true) ? 'checked' : '' }}>
                                            <div class="team-venue-meta">
                                                <label class="form-check-label fw-semibold">{{ $venue->name }}</label>
                                                <span class="meta-badge">{{ $venue->city }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">No hay sedes registradas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-user-group me-2 text-primary"></i>
                                Personal (Entrenadores)
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Entrenador principal <span class="text-danger">*</span></label>
                                    <select class="form-select" name="coach_primary" x-model="coachA" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($coaches as $coach)
                                            <option value="{{ $coach->id }}"
                                                :disabled="coachB === '{{ $coach->id }}'"
                                                {{ (string) $selectedCoachPrimary === (string) $coach->id ? 'selected' : '' }}>
                                                {{ $coach->name }} {{ $coach->lastname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Entrenador asistente</label>
                                    <select class="form-select" name="coach_secondary" x-model="coachB">
                                        <option value="">Selecciona...</option>
                                        @foreach($coaches as $coach)
                                            <option value="{{ $coach->id }}"
                                                :disabled="coachA === '{{ $coach->id }}'"
                                                {{ (string) $selectedCoachSecondary === (string) $coach->id ? 'selected' : '' }}>
                                                {{ $coach->name }} {{ $coach->lastname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small">No se puede repetir el mismo entrenador en ambos campos.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-users me-2 text-primary"></i>
                                Jugadores
                            </div>
                            <div class="text-muted small">
                                Selecciona los jugadores para esta plantilla ({{ count($selectedPlayerIds) }} seleccionados).
                            </div>

                            <div class="row g-3 mt-2 {{ $isEdit ? 'team-edit-players' : '' }}">
                                @forelse($players as $player)
                                    @php($isChecked = in_array($player->id, $selectedPlayerIds, true))
                                    <div class="col-12 col-md-6 {{ $isEdit ? 'col-lg-3' : 'col-lg-4' }}">
                                        <label class="team-player-card {{ $isChecked ? 'is-selected' : '' }}">
                                            <input
                                                class="form-check-input team-player-checkbox"
                                                type="checkbox"
                                                name="players[]"
                                                value="{{ $player->id }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                            >
                                            <span class="team-player-number">{{ $player->dorsal ?? '-' }}</span>
                                            <span class="team-player-meta">
                                                <span class="team-player-name">{{ $player->name }} {{ $player->lastname }}</span>
                                                <span class="team-player-position">{{ $positionOptions[$player->position] ?? 'Sin posición' }}</span>
                                            </span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">No hay jugadores activos para asignar.</div>
                                @endforelse
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
