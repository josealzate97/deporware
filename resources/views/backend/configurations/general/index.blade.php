@extends('backend.layouts.main')

@section('title', 'Configuracion')

@push('styles')
    @vite(['resources/css/modules/configurations/general.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations/general.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'configurations.index',
                    'icon' => 'fa-solid fa-cog',
                    'label' => 'Configuracion'
                ]
            ])
        @endpush

        <div class="card p-4 section-hero">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
                    <div class="section-hero-icon">
                        <i class="fa-solid fa-cog"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-0">Configuraciones</h2>
                        <div class="text-muted small fw-bold">Centraliza parametros generales, rivales y puntos de analisis</div>
                    </div>
                </div>

                <div class="section-hero-actions mt-2 mt-lg-0">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 section-card"
            x-data='configurationGeneralForm({
                initial: @json($config),
                hasConfig: {{ $config ? 'true' : 'false' }},
                indexUrl: @json(route('configurations.index')),
                updateUrl: @json(route('configurations.update'))
            })'
        >
            @php
                $activeTab = 'general';
            @endphp
            @include('backend.configurations.partials.tabs')

            <div class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center mb-3">
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">
                        <i class="fa-solid fa-cog me-2 text-primary"></i>
                        Datos generales
                    </h5>
                    <p class="text-muted mb-0">Edita la informacion base, localizacion y preferencias.</p>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary config-edit-btn" x-show="!isEditing" @click="enableEdit" :disabled="isLoading">
                        <i class="fa fa-edit"></i> Editar
                    </button>

                    <button type="button" class="btn btn-danger" x-show="isEditing" @click="cancelEdit" :disabled="isSaving">
                        <i class="fa fa-trash"></i> Cancelar
                    </button>

                    <button type="button" class="btn btn-success" x-show="isEditing" @click="save" :disabled="isSaving">
                        <span x-show="!isSaving"><i class="fa fa-save"></i> Guardar</span>
                        <span x-show="isSaving"><i class="fa fa-save"></i> Guardando...</span>
                    </button>
                </div>
            </div>

            <div class="alert alert-info py-2 mb-3" x-show="isLoading">
                Cargando configuracion...
            </div>

            <form class="info-form" @submit.prevent="save">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-list me-2 text-primary"></i>
                                Informacion General
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Nombre comercial</label>
                                    <input type="text" class="form-control" x-model="form.name" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Razon social</label>
                                    <input type="text" class="form-control" x-model="form.legal_name" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Identificacion legal</label>
                                    <input type="text" class="form-control" x-model="form.legal_id" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Telefono</label>
                                    <input type="text" class="form-control mask-phone" x-model="form.phone" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" x-model="form.email" :disabled="!isEditing">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="info-section h-100">
                            <div class="info-section-title">
                                <i class="fa-solid fa-image me-2 text-primary"></i>
                                Logo
                            </div>

                            <div class="config-logo-panel">
                                <div class="config-logo-panel-header">
                                    <div>
                                        <div class="fw-semibold">Logo de la configuracion</div>
                                        <span class="config-logo-badge">JPG o PNG · Máx 5MB</span>
                                    </div>
                                    <div class="config-logo-actions">
                                        <label for="configuration_logo_file" class="btn btn-outline-primary btn-sm" :class="{ 'disabled': !isEditing }">
                                            <i class="fa-solid fa-upload me-1"></i> Subir
                                        </label>
                                        <button type="button" class="btn btn-outline-danger btn-sm" @click="removeLogo()" :disabled="!isEditing">
                                            <i class="fa-solid fa-trash me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>

                                <input
                                    id="configuration_logo_file"
                                    type="file"
                                    class="d-none"
                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                    @change="onLogoSelected($event)"
                                    :disabled="!isEditing"
                                >

                                <div class="config-logo-preview mt-3">
                                    <div class="config-logo-frame">
                                        <template x-if="logoPreviewUrl">
                                            <img :src="logoPreviewUrl" alt="Logo de la configuración" class="config-logo-image">
                                        </template>
                                        <template x-if="!logoPreviewUrl">
                                            <div class="config-logo-placeholder">No válido por el momento</div>
                                        </template>
                                    </div>
                                    <div class="config-logo-hint">Vista previa del logo.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-location-dot me-2 text-primary"></i>
                                Ubicacion
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Direccion</label>
                                    <input type="text" class="form-control" x-model="form.address" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pais</label>
                                    <select class="form-select" x-model="form.country" :disabled="!isEditing">
                                        <option value="">Selecciona un pais</option>
                                        @foreach ($countries as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ciudad</label>
                                    <input type="text" class="form-control" x-model="form.city" :disabled="!isEditing">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-cog me-2 text-primary"></i>
                                Preferencias
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Sitio web</label>
                                    <input type="text" class="form-control" x-model="form.website" :disabled="!isEditing">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Deporte principal</label>
                                    <select class="form-select" x-model="form.sport" :disabled="!isEditing">
                                        <option value="">Selecciona un deporte</option>
                                        @foreach ($sports as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Moneda</label>
                                    <select class="form-select" x-model="form.currency" :disabled="!isEditing">
                                        <option value="">Selecciona una moneda</option>
                                        @foreach ($currencies as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Zona horaria</label>
                                    <select class="form-select" x-model="form.timezone" :disabled="!isEditing">
                                        <option value="">Selecciona una zona horaria</option>
                                        @foreach ($timezones as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Locale</label>
                                    <select class="form-select" x-model="form.locale" :disabled="!isEditing">
                                        <option value="">Selecciona un locale</option>
                                        @foreach ($locales as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection
