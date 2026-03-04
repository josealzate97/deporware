@extends('backend.layouts.main')

@section('title', 'Personal Deportivo')

@push('styles')
    @vite(['resources/css/modules/users.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/validator.js', 'resources/js/modules/users.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'users.index',
                    'icon' => 'fa-solid fa-user',
                    'label' => 'Personal Deportivo'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">

                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Personal Deportivo</h2>
                        <div class="text-muted small fw-bold">Administra usuarios, roles y estado de acceso para personal deportivo</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('users.new') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus-circle me-2"></i> Crear Personal
                    </a>
                </div>

            </div>

        </div>

        <div x-data="infoModal()">
        <div class="card p-0 mt-4 section-card">

            <div class="section-toolbar">
                <div class="section-search">
                    <i class="fas fa-search"></i>
                    <label class="visually-hidden" for="usersSearch">Buscar personal</label>
                    <input type="text" class="form-control form-control-sm" id="usersSearch" placeholder="Buscar personal...">
                </div>
                <label class="visually-hidden" for="usersRoleFilter">Filtrar por rol</label>
                <select class="form-select form-select-sm section-filter" id="usersRoleFilter">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Tabla de Usuarios -->
            <div class="table-responsive">

                <table class="table table-borderless align-middle section-table">

                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th class="text-center">Rol</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($users as $user)

                            <tr data-id="{{ $user->id }}" data-role="{{ $user->role }}">
                                <td>
                                    <div class="fw-bold">{{ $user->name }} {{ $user->lastname }}</div>
                                    <div class="mt-1">
                                        <span class="meta-badge">{{ $user->username }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td class="text-center">
                                    {{ $user->role_label }}
                                </td>
                                <td>
                                    @if($user->status == \App\Models\User::ACTIVE)
                                        <span class="status-pill status-pill-success">Activo</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Inactivo</span>
                                    @endif
                                </td>

                                <td class="text-end">

                                    @if(Auth::check() && in_array(Auth::user()->role, 
                                    [\App\Models\User::ROLE_ROOT, \App\Models\User::ROLE_SPORT_MANAGER, \App\Models\User::ROLE_COACH], true))
                                        <button type="button" class="btn btn-icon text-primary"
                                            @click="openModal('{{ route('users.info', $user->id) }}?modal=1')"
                                            aria-label="Ver información de {{ $user->name }} {{ $user->lastname }}" title="Ver información">
                                            <i class="fas fa-circle-info"></i>
                                        </button>
                                        <a href="{{ route('users.info', $user->id) }}" class="btn btn-icon btn-icon-edit"
                                           aria-label="Editar usuario {{ $user->name }} {{ $user->lastname }}" title="Editar usuario {{ $user->name }} {{ $user->lastname }}">
                                             <i class="fas fa-edit mt-1"></i>
                                        </a>
                                    @endif

                                    @if(Auth::check() && in_array(Auth::user()->role, 
                                    [\App\Models\User::ROLE_ROOT, \App\Models\User::ROLE_SPORT_MANAGER, \App\Models\User::ROLE_COACH], true))
                                        
                                        @if($user->status == \App\Models\User::ACTIVE)
                                        
                                            <button class="btn btn-icon text-danger" data-id="{{ $user->id }}"
                                                aria-label="Desactivar usuario {{ $user->name }} {{ $user->lastname }}" title="Desactivar usuario {{ $user->name }} {{ $user->lastname }}"
                                                onclick="deleteUser(this.getAttribute('data-id'))">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        @else

                                            <button class="btn btn-icon text-success" data-id="{{ $user->id }}"
                                                aria-label="Activar usuario {{ $user->name }} {{ $user->lastname }}" title="Activar usuario {{ $user->name }} {{ $user->lastname }}"
                                                onclick="activateUser(this.getAttribute('data-id'))">
                                                <i class="fas fa-check"></i>
                                            </button>

                                        @endif

                                    @endif

                                </td>

                            </tr>

                        @endforeach

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
