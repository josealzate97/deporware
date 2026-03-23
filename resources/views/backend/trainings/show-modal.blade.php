<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fas fa-dumbbell"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Entrenamiento</h3>
            <div class="text-muted small fw-bold">Consulta el detalle general de la sesión</div>
        </div>
    </div>
</div>

@php($tacticOptions = \App\Models\Training::tacticObjectivesOptions())
@php($fisicOptions = \App\Models\Training::fisicObjectivesOptions())
@php($tecnicOptions = \App\Models\Training::tecnichObjectivesOptions())
@php($psychoOptions = \App\Models\Training::psychoObjectivesOptions())
@php($momentOptions = \App\Models\Training::momentOptions())
@php($documentUrl = $training->document ? \Illuminate\Support\Facades\Storage::url($training->document) : null)

<div class="card p-3 section-card">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="fw-semibold">Entrenamiento</div>
            <div>{{ $training->name }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Equipo</div>
            <div>{{ $training->getRelationValue('team')?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Fecha</div>
            <div>{{ $training->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Duración</div>
            <div>{{ $training->duration ? $training->duration . ' min' : '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Sede</div>
            <div>{{ $training->venue?->name ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Locación</div>
            <div>{{ $training->location ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Estado</div>
            @if($training->status == \App\Models\Training::ACTIVE)
                <span class="status-pill status-pill-success">Activo</span>
            @else
                <span class="status-pill status-pill-muted">Inactivo</span>
            @endif
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Momento</div>
            <div>{{ $momentOptions[$training->moment] ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Objetivo principal</div>
            <div>{{ $training->principal_obj ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Objetivo táctico</div>
            <div>{{ $tacticOptions[$training->tactic_obj] ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Objetivo físico</div>
            <div>{{ $fisicOptions[$training->fisic_obj] ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Objetivo técnico</div>
            <div>{{ $tecnicOptions[$training->tecnic_obj] ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Objetivo psicológico</div>
            <div>{{ $psychoOptions[$training->pyscho_obj] ?? '-' }}</div>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold">Documento</div>
            @if($documentUrl)
                <a href="{{ $documentUrl }}" target="_blank" rel="noopener">Ver documento</a>
            @else
                <div>-</div>
            @endif
        </div>
    </div>
</div>
