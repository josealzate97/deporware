@if($matches->count() > 0)
<div class="table-responsive responsive-stack-table">
    <table class="table table-borderless align-middle section-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Partido</th>
                <th>Detalle</th>
                <th>Resultado</th>
                <th>Estado</th>
                <th>Valoraciones</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matches as $match)
                <tr data-id="{{ $match->id }}">
                    <td data-label="Fecha">
                        <div class="fw-semibold">{{ $match->match_date?->format('Y-m-d') ?? '-' }}</div>
                        <div class="text-muted small fw-semibold">{{ $match->match_date?->format('H:i') ?? '-' }}</div>
                    </td>
                    @php($teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null)
                    @php($rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null)
                    @php($feedbackModel = $match->relationLoaded('feedback') ? $match->getRelation('feedback') : null)
                    @php($ratingModel = $match->relationLoaded('teamRating') ? $match->getRelation('teamRating') : null)
                    <td data-label="Partido">
                        <div class="fw-semibold">{{ $teamModel?->name ?? ($match->team ? 'Sin equipo vinculado' : '-') }}</div>
                        <div class="text-muted small fw-semibold">vs {{ $rivalModel?->name ?? ($match->rival ? 'Sin rival vinculado' : '-') }}</div>
                    </td>
                    <td data-label="Detalle">
                        <div><span class="meta-badge">{{ $sideOptions[$match->side] ?? '-' }}</span></div>
                        <div class="text-muted small fw-semibold mt-1">{{ $match->match_round ?: '-' }}</div>
                    </td>
                    <td data-label="Resultado">
                        @if($match->match_status === \App\Models\MatchModel::STATUS_SCHEDULED)
                            <span class="match-result-pill match-result-pill-pending">Pendiente</span>
                        @else
                            @php($resultLabel = $resultOptions[$match->match_result] ?? '-')
                            @php($resultPillClass = match ((int) $match->match_result) {
                                \App\Models\MatchModel::RESULT_WIN => 'match-result-pill-win',
                                \App\Models\MatchModel::RESULT_DRAW => 'match-result-pill-draw',
                                \App\Models\MatchModel::RESULT_LOSS => 'match-result-pill-loss',
                                default => 'match-result-pill-draw',
                            })
                            <div>
                                <span class="match-result-pill {{ $resultPillClass }}">{{ $resultLabel }}</span>
                            </div>
                            <div class="text-muted small mt-1">Marcador: <span class="fw-bold">{{ $match->final_score ?: '-' }}</span></div>
                        @endif
                    </td>
                    <td data-label="Estado">
                        @php($statusLabel = $statusOptions[$match->match_status] ?? 'Sin estado')
                        <span class="status-pill {{ $match->match_status === \App\Models\MatchModel::STATUS_COMPLETED ? 'status-pill-success' : 'status-pill-muted' }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td data-label="Valoraciones">
                        <div class="d-flex flex-wrap gap-1">
                            @if($feedbackModel)
                                <span class="meta-badge">Técnica</span>
                            @else
                                <span class="meta-badge text-muted">Sin técnica</span>
                            @endif

                            @if($ratingModel)
                                <span class="meta-badge">Aptitudinal</span>
                            @else
                                <span class="meta-badge text-muted">Sin aptitudinal</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end" data-label="Acciones">
                        <button type="button" class="btn btn-icon text-primary"
                            @click="openModal('{{ route('matches.show', $match->id) }}?modal=1')"
                            aria-label="Ver información del partido" title="Ver información">
                            <i class="fas fa-circle-info"></i>
                        </button>
                        <a href="{{ route('matches.edit', $match->id) }}" class="btn btn-icon btn-icon-edit"
                            aria-label="Editar partido" title="Editar partido">
                            <i class="fas fa-edit mt-1"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('backend.components.pagination', [
    'paginator' => $matches,
    'ariaLabel' => 'Paginador de partidos',
])
@else
<div class="matches-list-empty" role="status" aria-live="polite">
    <i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>
    <div class="matches-list-empty-title">No hay partidos registrados.</div>
    <div class="matches-list-empty-subtitle">Comienza creando tu primer partido para verlo en el listado.</div>
    <a href="{{ route('matches.new') }}" class="btn btn-success matches-list-empty-cta">
        <i class="fa-solid fa-plus-circle me-2"></i> Crear Partido
    </a>
</div>
@endif
