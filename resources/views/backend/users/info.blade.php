@extends('backend.layouts.main')

@section('title', 'Info Usuario')

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

        @php
            $userPayload = [
                'name' => $user->name,
                'username' => $user->username,
                'phone' => $user->phone,
                'role' => $user->role,
                'password' => $user->password,
                'showPassword' => false,
                'email' => $user->email,
                'birthday' => $user->birthday?->format('Y-m-d') ?? '',
                'hired_date' => $user->hired_date?->format('Y-m-d') ?? '',
                'id' => $user->id,
                'status' => $user->status,
                'venues' => $user->venues->pluck('id')->values(),
                'adminRoles' => [\App\Models\User::ROLE_ROOT, \App\Models\User::ROLE_SPORT_MANAGER],
            ];
        @endphp

        <div class="card p-4 mt-4 user-info-card"
            x-data='userForm(@json($userPayload))'
        >

            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Información del usuario</h5>
                    <p class="text-muted mb-0">Edita los datos personales, contacto y acceso.</p>
                </div>

                @if(Auth::check() && in_array(Auth::user()->role, 
                [\App\Models\User::ROLE_ROOT, \App\Models\User::ROLE_SPORT_MANAGER, \App\Models\User::ROLE_COACH], true))
                    <div class="d-flex gap-2 user-info-actions">
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

            <form class="form user-info-form" data-validate="app" novalidate @submit.prevent="saveUser">

                <div class="alert alert-danger mb-4" x-show="validationErrors.length" x-cloak>
                    <div class="fw-bold mb-1">Se encontraron errores en el formulario:</div>
                    <ul class="mb-0 ps-3">
                        <template x-for="(error, index) in validationErrors" :key="index">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <div class="row g-4">

                    <input type="hidden" name="status" x-model="form.status">

                    <div class="col-12">

                        <div class="user-info-section">

                            <div class="user-info-section-title">
                                <i class="fa-solid fa-id-card me-2 text-primary"></i>
                                Datos personales
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" x-model="form.name" :required="editMode" :disabled="!editMode">
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Usuario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" x-model="form.username" :required="editMode" :disabled="!editMode">
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Fecha de cumpleaños</label>
                                    <input type="date" class="form-control" x-model="form.birthday" :disabled="!editMode">
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <label class="form-label fw-bold">Fecha de contrato <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" x-model="form.hired_date" :required="editMode" :disabled="!editMode">
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

                            <div class="row g-4 mt-1">
                                <div class="col-12 col-lg-6">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control mask-phone" x-model="form.phone" :required="editMode" :disabled="!editMode">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Correo electrónico <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" x-model="form.email" :required="editMode" :disabled="!editMode">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Rol <span class="text-danger">*</span></label>
                                            <select class="form-select" x-model="form.role" :required="editMode" :disabled="!editMode">
                                                @foreach($roles as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6" x-show="editMode">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Nueva Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="user-new-password" x-model="form.new_password" @blur="validatePassword" :disabled="!editMode">
                                                <button class="btn btn-outline-secondary" type="button" data-password-toggle data-target="user-new-password" aria-label="Mostrar u ocultar contraseña" :disabled="!editMode">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-danger" id="user-new-password-message"></small>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Confirmar contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="user-new-password-confirm" x-model="confirmNewPassword" @blur="validatePassword" :disabled="!editMode">
                                                <button class="btn btn-outline-secondary" type="button" data-password-toggle data-target="user-new-password-confirm" aria-label="Mostrar u ocultar confirmación de contraseña" :disabled="!editMode">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-danger" id="user-new-password-confirm-message"></small>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-text">Mínimo 8 caracteres, una letra, un número y un carácter especial.</div>
                                            <ul class="password-checklist mt-2" id="user-new-password-checklist">
                                                <li data-rule="length">Mínimo 8 caracteres</li>
                                                <li data-rule="letter">Al menos una letra</li>
                                                <li data-rule="number">Al menos un número</li>
                                                <li data-rule="special">Al menos un carácter especial</li>
                                            </ul>
                                            <ul class="password-checklist mt-2" id="user-new-password-confirm-checklist">
                                                <li data-rule="match">Las contraseñas coinciden</li>
                                            </ul>
                                        </div>
                                    </div>
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

                            <div x-show="!adminRoles.includes(parseInt(form.role))">
                                <div class="row g-3 mt-1">
                                    @foreach($venues as $venue)
                                        @if($venue->status)
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <div class="form-check form-switch form-switch-lg venue-switch">
                                                    <input class="form-check-input" type="checkbox" :value="'{{ $venue->id }}'" x-model="form.venues" :disabled="!editMode">
                                                    <label class="form-check-label fw-bold">{{ $venue->name }}</label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="text-muted small mt-2">
                                    Selecciona una o varias sedes donde trabaja el personal.
                                </div>
                            </div>

                            <div class="text-muted small mt-2" x-show="adminRoles.includes(parseInt(form.role))">
                                Los roles Super Admin y Gerente Deportivo no requieren sedes asignadas.
                            </div>
                        </div>
                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection
