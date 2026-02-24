@extends('backend.layouts.main')

@section('title', 'Nuevo Usuario')

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

        <div class="card p-4 user-info-card">

            <div class="user-info-header">
                <div class="user-info-title">
                    <div class="user-avatar-lg">
                        <i class="fa fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Crear Personal Deportivo</h3>
                        <div class="text-muted fw-bold small user-info-subtitle">Crea un nuevo miembro del personal deportivo</div>
                    </div>
                </div>

                <a href="{{ route('users.index') }}" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="user-info-divider"></div>

            <form class="form user-info-form" method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="row g-4">

                    <div class="col-12">
                        <div class="user-info-section">
                            <div class="user-info-section-title">Datos personales</div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Nombres</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Usuario</label>
                                    <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="user-info-section">
                            <div class="user-info-section-title">Contacto y acceso</div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Teléfono</label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Correo electrónico</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Rol</label>
                                    <select class="form-select" name="rol" required>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" {{ old('rol') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
