@extends('backend.layouts.main')

@section('title', 'Configuración')

@push('styles')
    @vite(['resources/css/modules/configurations.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/configurations.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => [
                    'route' => 'configurations.index',
                    'icon' => 'fa-solid fa-cog',
                    'label' => 'Configuración'
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
                        <h2 class="fw-bold mb-0">Configuración</h2>
                        <div class="text-muted small fw-bold">Datos generales de tu escuela deportiva, localización y preferencias</div>
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
            x-data='configurationForm({
                initial: @json($config),
                hasConfig: {{ $config ? 'true' : 'false' }},
                indexUrl: @json(route('configurations.index')),
                updateUrl: @json(route('configurations.update'))
            })'
        >
            <div class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center mb-3">
                
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">
                        <i class="fa-solid fa-cog me-2 text-primary"></i>
                        Datos generales
                    </h5>
                    <p class="text-muted mb-0">Edita la información base, localización y preferencias.</p>
                </div>

                <div class="d-flex gap-2">

                    <button type="button" class="btn btn-primary" x-show="!isEditing" @click="enableEdit" :disabled="isLoading">
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
                Cargando configuración...
            </div>

            <form class="info-form" @submit.prevent="save">
                <div class="row g-4">

                    <div class="col-12">

                        <div class="info-section">

                            <div class="info-section-title">
                                <i class="fa-solid fa-list me-2 text-primary"></i>
                                Información General
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Nombre comercial</label>
                                    <input type="text" class="form-control" x-model="form.name" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Razón social</label>
                                    <input type="text" class="form-control" x-model="form.legal_name" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Identificación legal</label>
                                    <input type="text" class="form-control" x-model="form.legal_id" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" class="form-control mask-phone" x-model="form.phone" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" x-model="form.email" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Sitio web</label>
                                    <input type="text" class="form-control" x-model="form.website" :disabled="!isEditing">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-location-dot me-2 text-primary"></i>
                                Ubicación
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-semibold">Dirección</label>
                                    <input type="text" class="form-control" x-model="form.address" :disabled="!isEditing">
                                </div>

                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">País</label>
                                    <select class="form-select" x-model="form.country" :disabled="!isEditing">
                                        <option value="">Selecciona un país</option>
                                        @foreach ($countries as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Ciudad</label>
                                    <input type="text" class="form-control" x-model="form.city" :disabled="!isEditing">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-section">
                            <div class="info-section-title">
                                <i class="fa-solid fa-cog me-2 text-primary"></i>
                                Preferencias
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Logo (URL o ruta)</label>
                                    <input type="text" class="form-control" x-model="form.logo" :disabled="!isEditing">
                                </div>
                                
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Deporte principal</label>
                                    <select class="form-select" x-model="form.sport" :disabled="!isEditing">
                                        <option value="">Selecciona un deporte</option>
                                        @foreach ($sports as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Moneda</label>
                                    <select class="form-select" x-model="form.currency" :disabled="!isEditing">
                                        <option value="">Selecciona una moneda</option>
                                        @foreach ($currencies as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Zona horaria</label>
                                    <select class="form-select" x-model="form.timezone" :disabled="!isEditing">
                                        <option value="">Selecciona una zona horaria</option>
                                        @foreach ($timezones as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-lg-4">
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
