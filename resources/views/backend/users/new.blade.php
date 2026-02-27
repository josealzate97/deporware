@extends('backend.layouts.main')

@section('title', 'Personal Deportivo')

@push('styles')
    @vite(['resources/css/modules/users.css'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'users.new',
                    'icon' => 'fas fa-user-plus',
                    'label' => 'Crear Personal Deportivo'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="user-info-title">
                    <div class="user-avatar-lg">
                        <i class="fa fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Crear Personal Deportivo</h3>
                        <div class="text-muted fw-bold small user-info-subtitle">Crea un nuevo miembro del personal deportivo</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 user-info-card"
            x-data="{
                role: '{{ old('role') }}',
                adminRoles: [{{ \App\Models\User::ROLE_ROOT }}, {{ \App\Models\User::ROLE_SPORT_MANAGER }}]
            }"
        >
            <form class="form user-info-form" method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="row g-4">

                    <div class="col-12">

                        <div class="user-info-section">

                            <div class="user-info-section-title">
                                <i class="fa-solid fa-user me-2 text-primary"></i>
                                Datos personales
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Nombre completo</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Usuario</label>
                                    <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Fecha de contrato</label>
                                    <input type="date" class="form-control" name="hired_date" value="{{ old('hired_date') }}" required>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-12">
                        <div class="user-info-section">
                            <div class="user-info-section-title">
                                <i class="fa-solid fa-envelope me-2 text-primary"></i>
                                Contacto y acceso
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Teléfono</label>
                                    <input type="text" class="form-control mask-phone" name="phone" value="{{ old('phone') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Correo electrónico</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Rol</label>
                                    <select class="form-select" name="role" x-model="role" required>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Contraseña</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-12">
                        <div class="user-info-section">
                            <div class="user-info-section-title">
                                <i class="fa-solid fa-building-circle-check me-2 text-primary"></i>
                                Sedes asignadas
                            </div>

                            <div class="text-muted small mt-1" x-show="!adminRoles.includes(parseInt(role))">
                                Selecciona una o varias sedes donde trabaja el personal.
                            </div>

                            <div x-show="!adminRoles.includes(parseInt(role))">
                                <div class="row g-3 mt-1">
                                    @foreach($venues as $venue)
                                        @if($venue->status)
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="form-check form-switch form-switch-lg venue-switch">
                                                <input class="form-check-input" type="checkbox" name="venues[]" value="{{ $venue->id }}"
                                                    {{ in_array($venue->id, old('venues', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold">{{ $venue->name }}</label>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                            <div class="text-muted small mt-2" x-show="adminRoles.includes(parseInt(role))">
                                Los roles Super Admin y Gerente Deportivo no requieren sedes asignadas.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="my-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">
                        <i class="fa fa-save"></i>&nbsp;
                        Guardar Usuario
                    </button>
                </div>

            </form>

        </div>

    </div>

@endsection
