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

        <div x-data="infoModal()">
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

@endsection
