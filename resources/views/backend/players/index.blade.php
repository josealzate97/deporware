@extends('backend.layouts.main')

@section('title', 'Jugadores')

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
                    'route' => 'players.index',
                    'icon' => 'fa-solid fa-people-group',
                    'label' => 'Jugadores'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-people-group"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Lista de Jugadores</h2>
                        <div class="text-muted small fw-bold">Organiza, actualiza y controla la información deportiva de cada jugador</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('players.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Jugador
                    </a>
                </div>

            </div>

        </div>

        <div x-data="playersPage()">
            <div class="card p-0 mt-4 section-card">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle section-table">
                        <thead>
                            <tr>
                                <th>Jugador</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($players as $player)
                                <tr data-id="{{ $player->id }}">
                                    <td>
                                        <div class="fw-bold">{{ $player->name }} {{ $player->lastname }}</div>
                                    </td>
                                    <td>{{ $player->email ?? '-' }}</td>
                                    <td>{{ $player->phone ?? '-' }}</td>
                                    <td>
                                        @if($player->status == \App\Models\Player::ACTIVE)
                                            <span class="status-pill status-pill-success">Activo</span>
                                        @else
                                            <span class="status-pill status-pill-muted">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-icon text-primary"
                                            @click="openModal('{{ route('players.show', $player->id) }}?modal=1')"
                                            aria-label="Ver información de {{ $player->name }} {{ $player->lastname }}" title="Ver información">
                                            <i class="fas fa-circle-info"></i>
                                        </button>
                                        <a href="{{ route('players.edit', ['id' => $player->id, 'step' => 'player']) }}" class="btn btn-icon btn-icon-edit"
                                            aria-label="Editar jugador {{ $player->name }} {{ $player->lastname }}" title="Editar jugador {{ $player->name }} {{ $player->lastname }}">
                                            <i class="fas fa-edit mt-1"></i>
                                        </a>
                                        <button type="button" class="btn btn-icon text-success"
                                            @click="openObservation('{{ $player->id }}', @js($player->name . ' ' . $player->lastname))"
                                            aria-label="Agregar observación a {{ $player->name }} {{ $player->lastname }}" title="Agregar observación">
                                            <i class="fas fa-note-sticky"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon text-danger"
                                            @click="deletePlayer('{{ route('players.destroy', $player->id) }}')"
                                            aria-label="Eliminar jugador {{ $player->name }} {{ $player->lastname }}" title="Eliminar jugador">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay jugadores registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="info-overlay" x-show="open" x-transition.opacity x-cloak @click.self="closeModal">
                <div class="info-panel" :class="open ? 'is-open' : ''" x-show="open" x-transition>
                    <div class="info-header">
                        <span x-text="title"></span>
                        <button type="button" class="info-close" @click="closeModal">&times;</button>
                    </div>
                    <div class="info-body" x-html="content"></div>
                </div>
            </div>

            <div class="info-overlay" x-show="observationOpen" x-transition.opacity x-cloak @click.self="closeObservation">
                <div class="info-panel" :class="observationOpen ? 'is-open' : ''" x-show="observationOpen" x-transition>
                    <div class="info-header">
                        <span>Crear observación</span>
                        <button type="button" class="info-close" @click="closeObservation">&times;</button>
                    </div>
                    <div class="info-body">
                        <form method="POST" :action="`{{ route('players.observations.store', ['id' => '__ID__']) }}`.replace('__ID__', observationPlayerId)">
                            @csrf
                            <div class="info-section">
                                <div class="info-section-title">
                                    <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>
                                    <span x-text="observationPlayerName"></span>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Tipo</label>
                                        <select class="form-select" name="type" required>
                                            <option value="">Selecciona...</option>
                                            @foreach($observationTypes as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notas</label>
                                        <textarea class="form-control" name="notes" rows="4" placeholder="Escribe la observación..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success px-4 fw-bold">
                                    <i class="fa fa-save me-2"></i>
                                    Guardar observación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

@endsection
