@extends('backend.layouts.main')

@php($isEdit = $isEdit ?? false)
@php($training = $training ?? null)
@php($teams = $teams ?? collect())
@php($venues = $venues ?? collect())
@php($statusOptions = $statusOptions ?? \App\Models\Training::statusOptions())
@php($tacticObjectives = $tacticObjectives ?? \App\Models\Training::tacticObjectivesOptions())
@php($fisicObjectives = $fisicObjectives ?? \App\Models\Training::fisicObjectivesOptions())
@php($tecnicObjectives = $tecnicObjectives ?? \App\Models\Training::tecnichObjectivesOptions())
@php($psychoObjectives = $psychoObjectives ?? \App\Models\Training::psychoObjectivesOptions())
@php($momentOptions = $momentOptions ?? \App\Models\Training::momentOptions())
@php($playersByTeam = $playersByTeam ?? [])
@php($selectedAttendance = collect(old('attendance', $selectedAttendance ?? []))->map(fn($value) => (string) $value)->all())
@php($trainingDateValue = old('training_date', $training?->created_at?->format('Y-m-d H:i') ?? ''))
@php($trainingDateDate = $trainingDateValue ? \Carbon\Carbon::parse($trainingDateValue)->format('Y-m-d') : '')
@php($trainingDateTime = $trainingDateValue ? \Carbon\Carbon::parse($trainingDateValue)->format('H:i') : '')
@php($hasExistingDocument = $isEdit && !empty($training?->document))
@php($showExistingDocument = $hasExistingDocument && old('remove_document', '0') !== '1')
@php($existingDocumentPath = $hasExistingDocument ? $training->document : '')
@php($existingDocumentExtension = $existingDocumentPath ? strtoupper(pathinfo($existingDocumentPath, PATHINFO_EXTENSION)) : '')
@php($existingDocumentIsPdf = strtolower(pathinfo($existingDocumentPath, PATHINFO_EXTENSION)) === 'pdf')
@php($trainingObservations = $trainingObservations ?? collect())
@php($selectedObservation = $selectedObservation ?? null)
@php($isCoordinator = $isCoordinator ?? ((int) auth()->user()?->role === \App\Models\User::ROLE_COORDINATOR))

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

                <div class="section-hero-actions mt-2 mt-lg-0 d-flex gap-2">
                    @if($isEdit && $isCoordinator)
                        <a href="#training-observations" class="btn btn-outline-primary">
                            <i class="fa-solid fa-note-sticky me-2"></i> Observaciones
                        </a>
                    @endif
                    <a id="btn-download-training-template"
                       href="{{ asset('docs/plantilla-entrenamientos.xlsx') }}"
                       download
                       class="btn btn-download-template">
                        <i class="fa-solid fa-file-arrow-down me-2"></i> Descargar Plantilla
                    </a>
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
                    <div class="col-12 col-lg-7">
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

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Duración (min) <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" name="duration"
                                        value="{{ old('duration', $training?->duration ?? '') }}" required>
                                    @error('duration')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="divider my-2"></div>
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo táctico</label>
                                    <select class="form-select @error('tactic_obj') is-invalid @enderror" name="tactic_obj">
                                        <option value="">Selecciona...</option>
                                        @foreach($tacticObjectives as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('tactic_obj', $training?->tactic_obj ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tactic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo físico</label>
                                    <select class="form-select @error('fisic_obj') is-invalid @enderror" name="fisic_obj">
                                        <option value="">Selecciona...</option>
                                        @foreach($fisicObjectives as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('fisic_obj', $training?->fisic_obj ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('fisic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo técnico</label>
                                    <select class="form-select @error('tecnic_obj') is-invalid @enderror" name="tecnic_obj">
                                        <option value="">Selecciona...</option>
                                        @foreach($tecnicObjectives as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('tecnic_obj', $training?->tecnic_obj ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tecnic_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Objetivo psicológico</label>
                                    <select class="form-select @error('pyscho_obj') is-invalid @enderror" name="pyscho_obj">
                                        <option value="">Selecciona...</option>
                                        @foreach($psychoObjectives as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('pyscho_obj', $training?->pyscho_obj ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pyscho_obj')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">Momento</label>
                                    <select class="form-select @error('moment') is-invalid @enderror" name="moment">
                                        <option value="">Selecciona...</option>
                                        @foreach($momentOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string) old('moment', $training?->moment ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('moment')
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

                    <div class="col-12 col-lg-5">
                        <div class="training-side-panel">
                            <div class="training-side-panel-item surface-gradient-day">
                                <div class="training-side-panel-header">
                                    <div>
                                        <div class="training-side-panel-title">Archivos del entrenamiento</div>
                                        <p class="training-side-panel-subtitle mb-0">Sube la planificacion o evidencia de la sesion.</p>
                                    </div>
                                </div>

                                <div class="training-side-panel-body mt-3">
                                    <div class="match-file-item">
                                        <div class="match-file-item-header">
                                            <label class="form-label fw-semibold mb-0" for="training_document">Informe Entrenamiento</label>
                                            <span class="match-file-badge">PDF/DOCX/XLS/XLSX · Máx 5MB</span>
                                        </div>

                                        <input type="hidden" name="remove_document" id="remove_document" value="{{ old('remove_document', '0') }}">

                                        @if($hasExistingDocument)
                                            <div
                                                class="match-file-preview-card mt-3 {{ $showExistingDocument ? '' : 'd-none' }}"
                                                data-training-asset="document"
                                                data-has-existing="{{ $showExistingDocument ? '1' : '0' }}">
                                                <div class="match-file-preview-main">
                                                    <div class="match-file-thumb match-file-thumb-document">
                                                        <span>{{ $existingDocumentExtension ?: 'FILE' }}</span>
                                                    </div>
                                                    <div class="match-file-preview-copy">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <div class="match-file-preview-title">Informe</div>
                                                            <span class="status-pill status-pill-success">Informe actual cargado</span>
                                                        </div>
                                                        <div class="match-file-preview-text">
                                                            {{ $existingDocumentIsPdf ? 'Vista previa disponible en el navegador.' : 'Se abrira en una nueva pestana si el navegador soporta este formato.' }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="match-file-meta mt-3">
                                                    <button type="button" class="btn btn-sm match-file-remove-btn" data-training-remove>
                                                        <i class="fa-solid fa-trash me-1"></i> Quitar
                                                    </button>
                                                    <a href="{{ route('trainings.view.document', $training->id) }}" target="_blank" rel="noopener" class="btn btn-sm match-file-action-btn">
                                                        <i class="fa-solid fa-eye me-1"></i> Visualizar
                                                    </a>
                                                    <a href="{{ route('trainings.download.document', $training->id) }}" class="btn btn-sm match-file-download-btn">
                                                        <i class="fa-solid fa-download me-1"></i> Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="match-file-upload-wrap mt-3 {{ $showExistingDocument ? 'd-none' : '' }}" data-training-upload="document">
                                            <input type="file" class="form-control upload-control upload-control-gradient @error('document') is-invalid @enderror" name="document" id="training_document" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                            <div class="match-file-upload-hint mt-2" data-training-replacement-label>
                                                {{ $showExistingDocument ? 'Selecciona el nuevo informe para reemplazar el actual.' : 'Selecciona el archivo del informe.' }}
                                            </div>
                                        </div>

                                        @error('document')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="training-side-panel-item surface-gradient-day">
                                <div class="training-side-panel-header">
                                    <div>
                                        <div class="training-side-panel-title">Asistencia al entrenamiento</div>
                                        <p class="training-side-panel-subtitle mb-0">Marca los jugadores convocados.</p>
                                    </div>
                                </div>

                                <div class="training-side-panel-body">
                                    <div class="training-attendance-helper">
                                        Marca los jugadores que asistiran. Los que queden sin marcar se tomaran como ausentes.
                                    </div>

                                    @error('attendance')
                                        <div class="alert alert-danger py-2 px-3 mt-3 mb-0">{{ $message }}</div>
                                    @enderror

                                    <div class="training-attendance-empty mt-3" x-show="!selectedTeam">
                                        Selecciona primero el equipo para habilitar la lista de jugadores.
                                    </div>

                                    @foreach($teams as $team)
                                        @php($teamPlayers = $playersByTeam[$team->id] ?? [])
                                        <div class="row g-3 mt-3" x-show="selectedTeam === '{{ $team->id }}'" x-cloak>
                                            @forelse($teamPlayers as $player)
                                                <div class="col-12 col-md-6">
                                                    <label class="training-attendance-card h-100">
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
                                                </div>
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
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('trainings.index') }}" class="btn btn-danger btn-action">
                            <i class="fa-solid fa-xmark me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-action">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($isEdit)
            <div class="card p-4 mt-4 section-card" id="training-observations">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">Observaciones del entrenamiento</h3>
                        <div class="text-muted small fw-bold">Seguimiento cualitativo registrado por coordinación.</div>
                    </div>

                    @if($isCoordinator)
                        <a href="{{ route('trainings.edit', $training->id) }}#training-observations" class="btn btn-primary">
                            <i class="fa-solid fa-plus-circle me-2"></i>Nueva observación
                        </a>
                    @endif
                </div>

                <div class="row g-4">
                    @if($isCoordinator)
                        <div class="col-12 col-lg-5">
                            <div class="training-observation-form-card">
                                <div class="training-side-panel-title">
                                    {{ $selectedObservation ? 'Editar observación' : 'Crear observación' }}
                                </div>
                                <p class="training-side-panel-subtitle mb-0">
                                    {{ $selectedObservation ? 'Actualiza la nota seleccionada.' : 'Registra una observación sobre este entrenamiento.' }}
                                </p>

                                <form method="POST" action="{{ $selectedObservation ? route('trainings.observations.update', [$training->id, $selectedObservation->id]) : route('trainings.observations.store', $training->id) }}" class="mt-3">
                                    @csrf
                                    @if($selectedObservation)
                                        @method('PUT')
                                    @endif

                                    <label class="form-label fw-semibold" for="training-observation-note">Observación <span class="text-danger">*</span></label>
                                    <textarea
                                        id="training-observation-note"
                                        name="note"
                                        rows="6"
                                        class="form-control @error('note') is-invalid @enderror"
                                        required>{{ old('note', $selectedObservation?->note ?? '') }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        @if($selectedObservation)
                                            <a href="{{ route('trainings.edit', $training->id) }}#training-observations" class="btn btn-danger btn-action">
                                                <i class="fa-solid fa-xmark me-2"></i>Cancelar
                                            </a>
                                        @endif
                                        <button type="submit" class="btn btn-success btn-action">
                                            <i class="fa-solid fa-floppy-disk me-2"></i>{{ $selectedObservation ? 'Actualizar' : 'Guardar' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="col-12 {{ $isCoordinator ? 'col-lg-7' : '' }}">
                        <div class="d-flex flex-column gap-3">
                            @forelse($trainingObservations as $observation)
                                <div class="training-observation-card">
                                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <div class="training-observation-author">
                                                <i class="fa-solid fa-user-pen me-2"></i>{{ $observation->author?->name ?? 'Coordinador' }}
                                            </div>
                                            <div class="training-observation-date">
                                                {{ $observation->updated_at?->format('d/m/Y H:i') ?? '-' }}
                                            </div>
                                        </div>

                                        @if($isCoordinator)
                                            <a href="{{ route('trainings.edit', ['id' => $training->id, 'observation' => $observation->id]) }}#training-observations" class="btn btn-sm training-action-btn training-action-btn-edit">
                                                <i class="fa-solid fa-pen me-1"></i>Editar
                                            </a>
                                        @endif
                                    </div>

                                    <div class="training-observation-note mt-3">{{ $observation->note }}</div>
                                </div>
                            @empty
                                <div class="training-observation-empty">
                                    <i class="fa-solid fa-note-sticky"></i>
                                    No hay observaciones registradas para este entrenamiento.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection
