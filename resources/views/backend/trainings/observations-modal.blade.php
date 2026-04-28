<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-note-sticky"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Observaciones del entrenamiento</h3>
            <div class="text-muted small fw-bold">{{ $training->name }}</div>
        </div>
    </div>
</div>

@php($isCoordinator = $isCoordinator ?? false)
@php($selectedObservation = $selectedObservation ?? null)
@php($trainingObservations = $trainingObservations ?? collect())

<div class="card p-3 section-card training-attendance-modal-card">

    @if($isCoordinator)
        <div class="training-observation-form-card mb-4">
            <div class="training-side-panel-title">
                {{ $selectedObservation ? 'Editar observación' : 'Nueva observación' }}
            </div>
            <p class="training-side-panel-subtitle mb-0">
                {{ $selectedObservation ? 'Actualiza la nota seleccionada.' : 'Registra una observación sobre este entrenamiento.' }}
            </p>

            <form method="POST"
                  action="{{ $selectedObservation
                      ? route('trainings.observations.update', [$training->id, $selectedObservation->id])
                      : route('trainings.observations.store', $training->id) }}"
                  class="mt-3">
                @csrf
                @if($selectedObservation)
                    @method('PUT')
                @endif
                <input type="hidden" name="_from" value="modal">

                <label class="form-label fw-semibold" for="obs-modal-note">
                    Observación <span class="text-danger">*</span>
                </label>
                <textarea
                    id="obs-modal-note"
                    name="note"
                    rows="5"
                    class="form-control"
                    required>{{ $selectedObservation?->note ?? '' }}</textarea>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    @if($selectedObservation)
                        <button type="button" class="btn btn-danger btn-action"
                            onclick="window._openObservationsModal('{{ route('trainings.show', $training->id) }}?modal=observations')">
                            <i class="fa-solid fa-xmark me-2"></i>Cancelar
                        </button>
                    @endif
                    <button type="submit" class="btn btn-success btn-action">
                        <i class="fa-solid fa-floppy-disk me-2"></i>{{ $selectedObservation ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="divider mb-4"></div>
    @endif

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
                        <button type="button"
                            class="btn btn-sm btn-warning"
                            onclick="window._openObservationsModal('{{ route('trainings.show', $training->id) }}?modal=observations&observation={{ $observation->id }}')">
                            <i class="fa-solid fa-pen me-1"></i>Editar
                        </button>
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
