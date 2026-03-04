@extends('backend.layouts.main')

@section('title', 'Plantillas')

@push('styles')
    @vite(['resources/css/modules/teams.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/teams.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">
        @php($baseFilters = array_filter(['season' => $seasonFilter, 'year' => $yearFilter]))

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'teams.index',
                    'icon' => 'fa-solid fa-shield',
                    'label' => 'Plantillas'
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
                        <h2 class="fw-bold mb-0">Plantillas</h2>
                        <div class="text-muted small fw-bold">Organiza y controla las plantillas activas por equipo y temporada</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('teams.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Plantilla
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">
        <div class="card p-0 mt-4 section-card"
            x-data='teamsTable({
                destroyUrlTemplate: @json(route("teams.destroy", ["id" => "__ID__"])),
                activateUrlTemplate: @json(route("teams.activate", ["id" => "__ID__"]))
            })'
        >
            <div class="section-toolbar teams-toolbar">

                <div class="section-search">
                    <i class="fas fa-search"></i>
                    <label class="visually-hidden" for="teamsSearch">Buscar plantilla</label>
                    <input type="text" class="form-control form-control-sm" id="teamsSearch" placeholder="Buscar plantilla...">
                </div>

                <label class="visually-hidden" for="teamsStatusFilter">Filtrar por estado</label>
                <select class="form-select form-select-sm section-filter" id="teamsStatusFilter">
                    <option value="">Todas</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <label class="visually-hidden" for="teamsSeasonFilter">Temporada</label>
                <input
                    type="text"
                    id="teamsSeasonFilter"
                    class="form-control form-control-sm section-filter"
                    placeholder="Temporada"
                >
                <label class="visually-hidden" for="teamsYearFilter">Año</label>
                <input
                    type="text"
                    id="teamsYearFilter"
                    class="form-control form-control-sm section-filter"
                    placeholder="Año"
                >

            </div>

            <div class="teams-tabs my-4">
                <a
                    href="{{ route('teams.index', array_merge(['type' => 'competitive'], $baseFilters)) }}"
                    class="teams-tab {{ $activeType === 'competitive' ? 'is-active' : '' }}"
                >
                    Competitivo
                </a>
                <a
                    href="{{ route('teams.index', array_merge(['type' => 'formative'], $baseFilters)) }}"
                    class="teams-tab {{ $activeType === 'formative' ? 'is-active' : '' }}"
                >
                    Formativo
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless align-middle section-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Temporada</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teams as $team)
                            <tr data-id="{{ $team->id }}" data-status="{{ $team->status ? '1' : '0' }}">
                                <td class="fw-semibold">{{ $team->name }}</td>
                                <td>{{ $team->season }}</td>
                                <td>{{ $team->year }}</td>
                                <td>
                                    @if($team->status)
                                        <span class="status-pill status-pill-success">Activa</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon text-primary"
                                        @click="openModal('{{ route('teams.show', $team->id) }}?modal=1')"
                                        aria-label="Ver información de {{ $team->name }}" title="Ver información">
                                        <i class="fas fa-circle-info"></i>
                                    </button>
                                    <a href="{{ route('teams.edit', $team->id) }}" class="btn btn-icon btn-icon-edit"
                                       aria-label="Editar plantilla {{ $team->name }}" title="Editar plantilla {{ $team->name }}">
                                        <i class="fas fa-edit mt-1"></i>
                                    </a>
                                    <template x-if="rowStatus('{{ $team->id }}') === '1'">
                                        <button type="button" class="btn btn-icon text-danger"
                                            aria-label="Eliminar plantilla {{ $team->name }}" title="Eliminar plantilla {{ $team->name }}"
                                            @click="deleteTeam('{{ $team->id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </template>
                                    <template x-if="rowStatus('{{ $team->id }}') === '0'">
                                        <button type="button" class="btn btn-icon text-success"
                                            aria-label="Activar plantilla {{ $team->name }}" title="Activar plantilla {{ $team->name }}"
                                            @click="activateTeam('{{ $team->id }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay plantillas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

            <div class="info-overlay" x-show="open" x-transition.opacity x-cloak @click.self="closeModal">
                <div class="info-panel" x-show="open" x-transition>
                    <div class="info-header">
                        <span x-text="title"></span>
                        <button type="button" class="info-close" @click="closeModal">&times;</button>
                    </div>
                    <div class="info-body" x-html="content"></div>
                </div>
            </div>

        </div>

    </div>

@endsection
