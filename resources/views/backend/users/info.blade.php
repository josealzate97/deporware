@extends('backend.layouts.main')

@section('title', 'Info Usuario')

@push('styles')
    @vite(['resources/css/modules/users.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/users.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'users.index',
                    'icon' => 'fas fa-user',
                    'label' => 'Gestión de Personal Deportivo'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="user-info-title">
                    <div class="user-avatar-lg">
                        <i class="fa fa-user"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Personal Deportivo</h3>
                        <div class="text-muted fw-bold small user-info-subtitle">{{ $user->name }} {{ $user->lastname }}</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 user-info-card" x-data="userForm({
            name: '{{ $user->name }}',
            username: '{{ $user->username }}',
            phone: '{{ $user->phone }}',
            role: '{{ $user->role }}',
            password: '{{ $user->password }}',
            showPassword: false,
            email: '{{ $user->email }}',
            hired_date: '{{ $user->hired_date?->format('Y-m-d') ?? '' }}',
            id: '{{ $user->id }}',
            status: '{{ $user->status }}'
        })">

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Información del usuario</h5>
                    <p class="text-muted mb-0">Edita los datos personales, contacto y acceso.</p>
                </div>

                @if(Auth::check() && in_array(Auth::user()->role, 
                [\App\Models\User::ROLE_ROOT, \App\Models\User::ROLE_SPORT_MANAGER, \App\Models\User::ROLE_COACH], true))
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" x-show="!editMode" @click="enableEdit" :disabled="isSaving">
                            <i class="fa fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-danger" x-show="editMode" @click="cancelEdit" :disabled="isSaving">
                            <i class="fa fa-trash"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" x-show="editMode" @click="saveUser" :disabled="isSaving">
                            <span x-show="!isSaving"><i class="fa fa-save"></i> Guardar</span>
                            <span x-show="isSaving"><i class="fa fa-save"></i> Guardando...</span>
                        </button>
                    </div>
                @endif
            </div>

            <form class="form user-info-form" @submit.prevent="saveUser">

                <div class="row g-4">

                    <input type="hidden" name="status" x-model="form.status">

                    <div class="col-12">

                        <div class="user-info-section">

                            <div class="user-info-section-title">
                                <i class="fa-solid fa-id-card me-2 text-primary"></i>
                                Datos personales
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Nombre completo</label>
                                    <input type="text" class="form-control" x-model="form.name" :disabled="!editMode">
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Usuario</label>
                                    <input type="text" class="form-control" x-model="form.username" :disabled="!editMode">
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Fecha de contrato</label>
                                    <input type="date" class="form-control" x-model="form.hired_date" :disabled="!editMode">
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
                                    <input type="text" class="form-control" x-model="form.phone" :disabled="!editMode">
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Correo electrónico</label>
                                    <input type="text" class="form-control" x-model="form.email" :disabled="!editMode">
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Rol</label>
                                    <select class="form-select" x-model="form.role" :disabled="!editMode">
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12" x-show="editMode">
                                    <label class="form-label fw-bold">Nueva Contraseña</label>
                                    <input type="password" class="form-control" x-model="form.new_password" @change="validatePassword" :disabled="!editMode">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection
