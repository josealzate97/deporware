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
                <div class="players-toolbar">
                    <div class="players-toolbar-meta">
                        <span class="fw-bold">Resultados</span>
                        <span class="text-muted">
                            @if($players->total() > 0)
                                Mostrando {{ $players->firstItem() }}-{{ $players->lastItem() }} de {{ $players->total() }}
                            @else
                                Sin resultados
                            @endif
                        </span>
                    </div>
                    <form class="section-toolbar players-filters" method="GET" action="{{ route('players.index') }}">
                        <div class="section-search">
                            <i class="fas fa-search"></i>
                            <label class="visually-hidden" for="playersSearch">Buscar jugador</label>
                            <input
                                type="search"
                                id="playersSearch"
                                name="search"
                                class="form-control form-control-sm"
                                placeholder="Buscar jugador, teléfono o equipo..."
                                value="{{ $search ?? '' }}"
                            >
                        </div>

                        <label class="visually-hidden" for="playersPosition">Posición</label>
                        <select class="form-select form-select-sm section-filter" id="playersPosition" name="position" onchange="this.form.requestSubmit()">
                            <option value="">Todas</option>
                            @foreach($positionOptions as $key => $label)
                                <option value="{{ $key }}" {{ (string) ($selectedPosition ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <label class="visually-hidden" for="playersTeam">Equipo</label>
                        <select class="form-select form-select-sm section-filter" id="playersTeam" name="team" onchange="this.form.requestSubmit()">
                            <option value="">Todos</option>
                            @foreach($teamOptions as $teamId => $teamName)
                                <option value="{{ $teamId }}" {{ (string) ($selectedTeam ?? '') === (string) $teamId ? 'selected' : '' }}>{{ $teamName }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm section-filter-btn">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('players.index') }}" class="btn btn-sm section-clear-btn">
                            <i class="fas fa-rotate-left"></i> Limpiar
                        </a>

                    </form>
                </div>
                <div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle section-table">
                        <thead>
                            <tr>
                                <th>Jugador</th>
                                <th>Posición</th>
                                <th>Equipo</th>
                                <th>Edad - Año</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($players as $player)
                                <tr data-id="{{ $player->id }}">
                                    <td>
                                        <div class="fw-bold">{{ $player->name }} {{ $player->lastname }}</div>
                                        <div class="players-index-badges mt-1">
                                            <span class="player-badge-slate">
                                                <i class="fa-solid fa-envelope me-1"></i>{{ $player->email ?? '-' }}
                                            </span>
                                            <span class="player-badge-slate">
                                                <i class="fa-solid fa-phone me-1"></i>{{ $player->phone ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $positionLabels = $player->position_labels;
                                            $primaryPositionLabel = $positionLabels[0] ?? null;
                                            $extraPositionsCount = max(count($positionLabels) - 1, 0);
                                        @endphp
                                        @if($primaryPositionLabel)
                                            <div class="players-index-badges">
                                                <span class="player-badge-green players-index-badge-green">{{ $primaryPositionLabel }}</span>
                                                @if($extraPositionsCount > 0)
                                                    <span class="player-badge-green players-index-badge-green">+{{ $extraPositionsCount }}</span>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $roster = $player->rosters->first();
                                            $teamName = null;

                                            if ($roster) {
                                                $teamModel = $roster->relationLoaded('team')
                                                    ? $roster->getRelation('team')
                                                    : $roster->team()->first();

                                                $teamName = $teamModel?->name;
                                            }
                                        @endphp
                                        @if($teamName)
                                            <span class="player-badge-blue">{{ $teamName }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($player->birthdate)
                                            <span class="player-badge-violet">
                                                {{ $player->birthdate->format('Y') }} - {{ \Carbon\Carbon::parse($player->birthdate)->age }} años
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
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
                                    <button type="button" class="btn btn-icon text-warning"
                                        @click="openObservation('{{ $player->id }}', @js($player->name . ' ' . $player->lastname))"
                                        aria-label="Agregar observación a {{ $player->name }} {{ $player->lastname }}" title="Agregar observación">
                                        <i class="fas fa-note-sticky"></i>
                                    </button>
                                    @if($player->status == \App\Models\Player::ACTIVE)
                                        <button type="button" class="btn btn-icon text-danger"
                                            @click="openConfirm({
                                                title: 'Desactivar jugador',
                                                message: '¿Deseas desactivar este jugador?',
                                                action: '{{ route('players.destroy', $player->id) }}',
                                                method: 'DELETE',
                                                successMessage: 'Jugador desactivado.'
                                            })"
                                            aria-label="Desactivar jugador {{ $player->name }} {{ $player->lastname }}" title="Desactivar jugador">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-icon text-success"
                                            @click="openConfirm({
                                                title: 'Activar jugador',
                                                message: '¿Deseas activar este jugador?',
                                                action: '{{ route('players.activate', $player->id) }}',
                                                method: 'POST',
                                                successMessage: 'Jugador activado.'
                                            })"
                                            aria-label="Activar jugador {{ $player->name }} {{ $player->lastname }}" title="Activar jugador">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    </td>
                            </tr>
                        @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay jugadores registrados.</td>
                                </tr>
                        @endforelse
                        </tbody>
                        </table>
                    </div>
                    @include('backend.components.pagination', [
                        'paginator' => $players,
                        'ariaLabel' => 'Paginador de jugadores',
                    ])
                </div>
            </div>

            <div class="info-overlay" x-show="open === true" x-transition.opacity x-cloak @click.self="closeModal">
                <div class="info-panel" :class="open ? 'is-open' : ''" x-show="open === true" x-transition>
                    <div class="info-header">
                        <span x-text="title"></span>
                        <button type="button" class="info-close" @click="closeModal">&times;</button>
                    </div>
                    <div class="info-body" x-html="content ?? ''"></div>
                </div>
            </div>

            <div class="info-overlay" x-show="observationOpen === true" x-transition.opacity x-cloak @click.self="closeObservation">
                <div class="info-panel" :class="observationOpen ? 'is-open' : ''" x-show="observationOpen === true" x-transition>
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
                                        <textarea class="form-control" name="notes" rows="5" placeholder="Escribe la observación..."></textarea>
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
            <div class="info-overlay" x-show="confirmOpen === true" x-transition.opacity x-cloak @click.self="closeConfirm">
                <div class="info-panel" :class="confirmOpen ? 'is-open' : ''" x-show="confirmOpen === true" x-transition>
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
        </div>

@endsection
