@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($training = $training ?? null)
@php($teams = $teams ?? collect())
@php($venues = $venues ?? collect())
@php($statusOptions = $statusOptions ?? \App\Models\Training::statusOptions())
@php($playersByTeam = $playersByTeam ?? [])
@php($selectedAttendance = collect(old('attendance', $selectedAttendance ?? []))->map(fn($value) => (string) $value)->all())
@php($trainingDateValue = old('training_date', $training?->created_at?->format('Y-m-d H:i') ?? ''))
@php($trainingDateDate = $trainingDateValue ? \Carbon\Carbon::parse($trainingDateValue)->format('Y-m-d') : '')
@php($trainingDateTime = $trainingDateValue ? \Carbon\Carbon::parse($trainingDateValue)->format('H:i') : '')

@section('title', $isEdit ? 'Editar Entrenamiento' : 'Nuevo Entrenamiento')

@push('styles')
    @vite(['resources/css/modules/trainings.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/trainings.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => $isEdit ? 'trainings.index' : 'trainings.new',
                    'icon' => 'fas fa-dumbbell',
                    'label' => $isEdit ? 'Editar Entrenamiento' : 'Nuevo Entrenamiento'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">{{ $isEdit ? 'Editar Entrenamiento' : 'Nuevo Entrenamiento' }}</h2>
                        <div class="text-muted small fw-bold">
                            {{ $isEdit ? 'Actualiza la planificación, la asistencia y la información general de la sesión' : 'Registra una nueva sesión de entrenamiento con sus objetivos y convocatoria' }}
                        </div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('trainings.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>

            </div>

        </div>

        <div class="card p-4 mt-4 section-card">
            <form class="info-form" data-validate="app" novalidate method="POST"
                action="{{ $isEdit ? route('trainings.update', $training?->id) : route('trainings.store') }}"
                enctype="multipart/form-data"
                x-data="{ selectedTeam: @js(old('team', $training?->team ?? '')) }">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <div class="fw-bold mb-1">Se encontraron errores en el formulario:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif

                <div class="row g-4">
                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                                Datos generales
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Fecha del entrenamiento <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="date" class="form-control @error('training_date') is-invalid @enderror" id="training_date_date"
                                                value="{{ $trainingDateDate }}" required>
                                        </div>
                                        <div class="col-5">
                                            <input type="time" class="form-control @error('training_date') is-invalid @enderror" id="training_date_time"
                                                value="{{ $trainingDateTime }}" step="1800" required>
                                        </div>
                                    </div>
                                    <input type="hidden" name="training_date" id="training_date" value="{{ $trainingDateValue }}" required>
                                    @error('training_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-lg-5">
                                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ old('name', $training?->name ?? '') }}" maxlength="250" required>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('status', $training?->status ?? \App\Models\Training::ACTIVE) === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Equipo <span class="text-danger">*</span></label>
                                    <select class="form-select @error('team') is-invalid @enderror" name="team" x-model="selectedTeam" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ (string) old('team', $training?->team ?? '') === (string) $team->id ? 'selected' : '' }}>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('team')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Sede</label>
                                    <select class="form-select @error('venue') is-invalid @enderror" name="venue">
                                        <option value="">Selecciona...</option>
                                        @foreach($venues as $venue)
                                            <option value="{{ $venue->id }}" {{ (string) old('venue', $training?->venue ?? '') === (string) $venue->id ? 'selected' : '' }}>
                                                {{ $venue->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('venue')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Locación</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location"
                                        value="{{ old('location', $training?->location ?? '') }}" maxlength="250">
                                    @error('location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Duración (min) <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" name="duration"
                                        value="{{ old('duration', $training?->duration ?? '') }}" required>
                                    @error('duration')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Objetivo principal</label>
                                    <input type="number" min="0" class="form-control @error('principal_obj') is-invalid @enderror" name="principal_obj"
                                        value="{{ old('principal_obj', $training?->principal_obj ?? '') }}">
                                    @error('principal_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Objetivo táctico</label>
                                    <input type="number" min="0" class="form-control @error('tactic_obj') is-invalid @enderror" name="tactic_obj"
                                        value="{{ old('tactic_obj', $training?->tactic_obj ?? '') }}">
                                    @error('tactic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">Objetivo físico</label>
                                    <input type="number" min="0" class="form-control @error('fisic_obj') is-invalid @enderror" name="fisic_obj"
                                        value="{{ old('fisic_obj', $training?->fisic_obj ?? '') }}">
                                    @error('fisic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo técnico</label>
                                    <input type="number" min="0" class="form-control @error('tecnic_obj') is-invalid @enderror" name="tecnic_obj"
                                        value="{{ old('tecnic_obj', $training?->tecnic_obj ?? '') }}">
                                    @error('tecnic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo psicológico</label>
                                    <input type="number" min="0" class="form-control @error('pyscho_obj') is-invalid @enderror" name="pyscho_obj"
                                        value="{{ old('pyscho_obj', $training?->pyscho_obj ?? '') }}">
                                    @error('pyscho_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Momento</label>
                                    <input type="number" min="0" class="form-control @error('moment') is-invalid @enderror" name="moment"
                                        value="{{ old('moment', $training?->moment ?? '') }}">
                                    @error('moment')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Documento</label>
                                    <input type="file" class="form-control @error('document') is-invalid @enderror" name="document" id="training_document" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    <div class="form-text">
                                        @if($isEdit && !empty($training?->document))
                                            Ya existe un documento guardado. Si subes uno nuevo, reemplazará el actual.
                                        @else
                                            Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX.
                                        @endif
                                    </div>
                                    @error('document')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', $training?->notes ?? '') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-user-check me-2 text-primary"></i>
                                Asistencia al entrenamiento
                            </div>

                            <div class="training-attendance-helper">
                                Marca los jugadores que asistirán. Los que queden sin marcar se tomarán como ausentes.
                            </div>

                            @error('attendance')
                                <div class="alert alert-danger py-2 px-3 mt-3 mb-0">{{ $message }}</div>
                            @enderror

                            <div class="training-attendance-empty mt-3" x-show="!selectedTeam">
                                Selecciona primero el equipo para habilitar la lista de jugadores.
                            </div>

                            @foreach($teams as $team)
                                @php($teamPlayers = $playersByTeam[$team->id] ?? [])
                                <div class="training-attendance-grid mt-3" x-show="selectedTeam === '{{ $team->id }}'" x-cloak>
                                    @forelse($teamPlayers as $player)
                                        <label class="training-attendance-card">
                                            <input class="form-check-input training-attendance-check" type="checkbox" name="attendance[]"
                                                value="{{ $player['id'] }}"
                                                x-bind:disabled="selectedTeam !== '{{ $team->id }}'"
                                                {{ in_array((string) $player['id'], $selectedAttendance, true) ? 'checked' : '' }}>
                                            <div class="training-attendance-content">
                                                <div class="training-attendance-name">{{ $player['name'] }}</div>
                                                <div class="training-attendance-meta">
                                                    <span>#{{ $player['dorsal'] ?: '-' }}</span>
                                                    <span>{{ $player['position'] ?: 'Sin posición' }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="training-attendance-empty">
                                            Este equipo no tiene jugadores activos en su roster.
                                        </div>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('trainings.index') }}" class="btn btn-danger btn-action">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        {{ $isEdit ? 'Actualizar entrenamiento' : 'Guardar entrenamiento' }}
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
