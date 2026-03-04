@extends('backend.layouts.main')

@section('title', 'Sedes')

@push('styles')
    @vite(['resources/css/modules/venues.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/venues.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'venues.index',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'label' => 'Sedes'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-building-circle-check"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Sedes Deportivas</h2>
                        <div class="text-muted small fw-bold">Gestiona las sedes donde opera tu escuela deportiva</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('venues.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Sede
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">
        <div class="card p-0 mt-4 section-card"
            x-data='venuesTable({
                destroyUrlTemplate: @json(route("venues.destroy", ["id" => "__ID__"])),
                activateUrlTemplate: @json(route("venues.activate", ["id" => "__ID__"]))
            })'
        >

            <div class="section-toolbar">
                <div class="section-search">
                    <i class="fas fa-search"></i>
                    <label class="visually-hidden" for="venuesSearch">Buscar sede</label>
                    <input type="text" class="form-control form-control-sm" id="venuesSearch" placeholder="Buscar sede...">
                </div>
                <label class="visually-hidden" for="venuesStatusFilter">Filtrar por estado</label>
                <select class="form-select form-select-sm section-filter" id="venuesStatusFilter">
                    <option value="">Todas</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="table-responsive">

                <table class="table table-borderless align-middle section-table">

                    <thead>
                        <tr>
                            <th>Sede</th>
                            <th>Dirección</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($venues as $venue)
                            <tr data-id="{{ $venue->id }}" data-status="{{ $venue->status ? '1' : '0' }}">
                                <td>
                                    <div class="fw-bold">{{ $venue->name }}</div>
                                    <div class="mt-1">
                                        <span class="meta-badge">{{ $venue->city }}</span>
                                    </div>
                                </td>
                                <td>{{ $venue->address }}</td>
                                <td>
                                    @if($venue->status)
                                        <span class="status-pill status-pill-success">Activa</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon text-primary"
                                        @click="openModal('{{ route('venues.show', $venue->id) }}?modal=1')"
                                        aria-label="Ver información de {{ $venue->name }}" title="Ver información">
                                        <i class="fas fa-circle-info"></i>
                                    </button>
                                    <a href="{{ route('venues.edit', $venue->id) }}" class="btn btn-icon btn-icon-edit"
                                       aria-label="Editar sede {{ $venue->name }}" title="Editar sede {{ $venue->name }}">
                                        <i class="fas fa-edit mt-1"></i>
                                    </a>
                                    @if($venue->name === 'Sede Principal')
                                        <button type="button" class="btn btn-icon text-muted" disabled
                                            aria-label="Sede Principal protegida" title="Sede Principal protegida">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @else
                                        <template x-if="rowStatus('{{ $venue->id }}') === '1'">
                                            <button type="button" class="btn btn-icon text-danger"
                                                aria-label="Eliminar sede {{ $venue->name }}" title="Eliminar sede {{ $venue->name }}"
                                                @click="deleteVenue('{{ $venue->id }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </template>
                                        <template x-if="rowStatus('{{ $venue->id }}') === '0'">
                                            <button type="button" class="btn btn-icon text-success"
                                                aria-label="Activar sede {{ $venue->name }}" title="Activar sede {{ $venue->name }}"
                                                @click="activateVenue('{{ $venue->id }}')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </template>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay sedes registradas.</td>
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

        </div>

    </div>

@endsection
