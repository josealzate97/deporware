@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($match = $match ?? null)
@php($teams = $teams ?? collect())
@php($rivals = $rivals ?? collect())
@php($venues = $venues ?? collect())
@php($attackPoints = $attackPoints ?? collect())
@php($defensivePoints = $defensivePoints ?? collect())
@php($statusOptions = $statusOptions ?? \App\Models\MatchModel::statusOptions())
@php($resultOptions = $resultOptions ?? \App\Models\MatchModel::resultOptions())
@php($sideOptions = $sideOptions ?? \App\Models\MatchModel::sideOptions())
@php($formationOptions = $formationOptions ?? \App\Models\MatchModel::formationOptions())
@php($defaultStatus = \App\Models\MatchModel::STATUS_SCHEDULED)
@php($statusCompleted = \App\Models\MatchModel::STATUS_COMPLETED)
@php($statusScheduled = \App\Models\MatchModel::STATUS_SCHEDULED)

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
            <form class="info-form" method="POST"
                action="{{ $isEdit ? route('matches.update', $match?->id) : route('matches.store') }}"
                enctype="multipart/form-data"
                x-data="{
                    status: @js(old('match_status', $match->match_status ?? $defaultStatus)),
                    get isCompleted() { return Number(this.status) === {{ $statusCompleted }} },
                    get isScheduled() { return Number(this.status) === {{ $statusScheduled }} },
                    get requiresResult() { return !this.isScheduled },
                }">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                                Datos basicos
                            </div>

                            <div class="row g-3 mt-1">
                                @php($matchDateValue = old('match_date', $match?->match_date?->format('Y-m-d H:i') ?? ''))
                                @php($matchDateDate = $matchDateValue ? \Carbon\Carbon::parse($matchDateValue)->format('Y-m-d') : '')
                                @php($matchDateTime = $matchDateValue ? \Carbon\Carbon::parse($matchDateValue)->format('H:i') : '')
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Fecha del partido <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="date" class="form-control" id="match_date_date"
                                                value="{{ $matchDateDate }}" required>
                                        </div>
                                        <div class="col-5">
                                            <input type="time" class="form-control" id="match_date_time" step="1800"
                                                value="{{ $matchDateTime }}" required>
                                        </div>
                                    </div>
                                    <input type="hidden" name="match_date" id="match_date" value="{{ $matchDateValue }}" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Jornada</label>
                                    <input type="text" class="form-control" name="match_round"
                                        value="{{ old('match_round', $match->match_round ?? '') }}">
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_status" x-model="status" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('match_status', $match->match_status ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Equipo <span class="text-danger">*</span></label>
                                    <div class="autocomplete" data-autocomplete='@json($teams->map(fn($team) => ['id' => $team->id, 'name' => $team->name])->values())'>
                                        <input type="text" class="form-control" data-autocomplete-input
                                            value="{{ old('team_name') }}" placeholder="Escribe para buscar" autocomplete="off" required>
                                        <input type="hidden" name="team" data-autocomplete-hidden value="{{ old('team', $match->team ?? '') }}">
                                        <div class="autocomplete-list" data-autocomplete-list></div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Rival <span class="text-danger">*</span></label>
                                    <div class="autocomplete" data-autocomplete='@json($rivals->map(fn($rival) => ['id' => $rival->id, 'name' => $rival->name])->values())'>
                                        <input type="text" class="form-control" data-autocomplete-input
                                            value="{{ old('rival_name') }}" placeholder="Escribe para buscar" autocomplete="off" required>
                                        <input type="hidden" name="rival" data-autocomplete-hidden value="{{ old('rival', $match->rival ?? '') }}">
                                        <div class="autocomplete-list" data-autocomplete-list></div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Sede</label>
                                    <select class="form-select" name="venue">
                                        <option value="">Selecciona...</option>
                                        @foreach($venues as $venue)
                                            <option value="{{ $venue->id }}" {{ (string) old('venue', $match->venue ?? '') === (string) $venue->id ? 'selected' : '' }}>
                                                {{ $venue->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Locacion</label>
                                    <input type="text" class="form-control" name="location"
                                        value="{{ old('location', $match->location ?? '') }}">
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">Local / Visitante <span class="text-danger">*</span></label>
                                    <select class="form-select" name="side" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($sideOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('side', $match->side ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">Resultado Juego <span class="text-danger" x-show="requiresResult">*</span></label>
                                    <select class="form-select" name="match_result" x-bind:required="requiresResult" x-bind:disabled="isScheduled">
                                        <option value="">Selecciona...</option>
                                        @foreach($resultOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('match_result', $match->match_result ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">Marcador <span class="text-danger" x-show="requiresResult">*</span></label>
                                    <input type="text" class="form-control" name="final_score"
                                        value="{{ old('final_score', $match->final_score ?? '') }}"
                                        x-bind:required="requiresResult"
                                        x-bind:disabled="isScheduled"
                                        placeholder="2-1">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control" name="match_notes" rows="5">{{ old('match_notes', $match->match_notes ?? '') }}</textarea>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Informe Partido <span class="text-danger" x-show="requiresResult">*</span></label>
                                    <input type="file" class="form-control upload-control" name="match_file" x-bind:required="requiresResult" x-bind:disabled="isScheduled" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Foto equipo</label>
                                    <input type="file" class="form-control upload-control" name="team_photo" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" x-show="isCompleted" x-cloak>
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-shield-halved me-2 text-primary"></i>
                                Feedback del partido
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Formacion <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_feedback[match_formation]" x-bind:required="isCompleted">
                                        <option value="">Selecciona...</option>
                                        @foreach($formationOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('match_feedback.match_formation', $match?->feedback?->match_formation ?? '') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Puntos fuertes ofensivos <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_feedback[attack_strengths]" x-bind:required="isCompleted">
                                        <option value="">Selecciona...</option>
                                        @foreach($attackPoints as $point)
                                            <option value="{{ $point->id }}" {{ (string) old('match_feedback.attack_strengths', $match?->feedback?->attack_strengths ?? '') === (string) $point->id ? 'selected' : '' }}>
                                                {{ $point->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Puntos debiles ofensivos <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_feedback[attack_weaknesses]" x-bind:required="isCompleted">
                                        <option value="">Selecciona...</option>
                                        @foreach($attackPoints as $point)
                                            <option value="{{ $point->id }}" {{ (string) old('match_feedback.attack_weaknesses', $match?->feedback?->attack_weaknesses ?? '') === (string) $point->id ? 'selected' : '' }}>
                                                {{ $point->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Puntos fuertes defensivos <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_feedback[defense_strengths]" x-bind:required="isCompleted">
                                        <option value="">Selecciona...</option>
                                        @foreach($defensivePoints as $point)
                                            <option value="{{ $point->id }}" {{ (string) old('match_feedback.defense_strengths', $match?->feedback?->defense_strengths ?? '') === (string) $point->id ? 'selected' : '' }}>
                                                {{ $point->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Puntos debiles defensivos <span class="text-danger">*</span></label>
                                    <select class="form-select" name="match_feedback[defense_weaknesses]" x-bind:required="isCompleted">
                                        <option value="">Selecciona...</option>
                                        @foreach($defensivePoints as $point)
                                            <option value="{{ $point->id }}" {{ (string) old('match_feedback.defense_weaknesses', $match?->feedback?->defense_weaknesses ?? '') === (string) $point->id ? 'selected' : '' }}>
                                                {{ $point->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control" name="match_feedback[notes]" rows="5">{{ old('match_feedback.notes', $match?->feedback?->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" x-show="isCompleted" x-cloak>
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-user-check me-2 text-primary"></i>
                                Feedback del tecnico
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Equipo con arbitro <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="10" class="form-control"
                                        name="match_team_rating[referee_rating]" x-bind:required="isCompleted"
                                        value="{{ old('match_team_rating.referee_rating', $match?->teamRating?->referee_rating ?? '') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Equipo con companeros <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="10" class="form-control"
                                        name="match_team_rating[teammates_rating]" x-bind:required="isCompleted"
                                        value="{{ old('match_team_rating.teammates_rating', $match?->teamRating?->teammates_rating ?? '') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Equipo con rivales <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="10" class="form-control"
                                        name="match_team_rating[opponents_rating]" x-bind:required="isCompleted"
                                        value="{{ old('match_team_rating.opponents_rating', $match?->teamRating?->opponents_rating ?? '') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Equipo con grada <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="10" class="form-control"
                                        name="match_team_rating[fans_rating]" x-bind:required="isCompleted"
                                        value="{{ old('match_team_rating.fans_rating', $match?->teamRating?->fans_rating ?? '') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Equipo con tecnico <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="10" class="form-control"
                                        name="match_team_rating[coach_rating]" x-bind:required="isCompleted"
                                        value="{{ old('match_team_rating.coach_rating', $match?->teamRating?->coach_rating ?? '') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control" name="match_team_rating[notes]" rows="5">{{ old('match_team_rating.notes', $match?->teamRating?->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('matches.index') }}" class="btn btn-danger btn-action">
                            <i class="fa-solid fa-xmark me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-action">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection
