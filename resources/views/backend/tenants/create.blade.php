@extends('backend.layouts.main')

@section('title', 'Nueva Escuela')

@push('scripts')
    @vite(['resources/js/modules/tenants.js'])
@endpush

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => ['route' => 'tenants.index', 'icon' => 'fa-solid fa-building', 'label' => 'Escuelas'],
                'page'    => ['label' => 'Nueva Escuela']
            ])
        @endpush

        <div class="card p-4 section-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="section-hero-icon">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0">Nueva Escuela</h2>
                    <div class="text-muted small fw-bold">Registra una nueva escuela (tenant) en el sistema</div>
                </div>
            </div>
        </div>

        <div class="card p-4 tenant-form-card" style="max-width:560px">
            <form method="POST" action="{{ route('tenants.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="tenantName"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="ej. Escuela Fútbol Norte" required
                           autocomplete="off">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Slug editable solo en creación --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="tenantSlug">
                        Identificador de acceso
                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.68rem">Solo letras, números y _</span>
                    </label>
                    <div class="input-group">
                        <input type="text" name="slug" id="tenantSlug"
                               class="form-control font-monospace @error('slug') is-invalid @enderror"
                               value="{{ old('slug') }}"
                               placeholder="se completará automáticamente…"
                               maxlength="75"
                               autocomplete="off"
                               spellcheck="false">
                        <button type="button" class="btn btn-outline-secondary" id="slugResetBtn" title="Volver a generar desde el nombre">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-2 p-2 rounded border border-warning-subtle bg-warning-subtle text-warning-emphasis small">
                        <div>Identificador final: <code id="slugFinalPreview" class="fw-bold">…</code> <span class="text-muted">(número consecutivo asignado al guardar)</span></div>
                        <div class="mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i><strong>No se podrá cambiar después de crearla.</strong></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipo de instalación <span class="text-danger">*</span></label>
                    <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                        <option value="1" {{ old('installation_type', '1') == '1' ? 'selected' : '' }}>Escuela</option>
                        <option value="2" {{ old('installation_type') == '2' ? 'selected' : '' }}>Club</option>
                        <option value="3" {{ old('installation_type') == '3' ? 'selected' : '' }}>Liga</option>
                    </select>
                    @error('installation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2 justify-content-center mt-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check me-2"></i> Crear Escuela
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-danger px-4"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                </div>
            </form>
        </div>

    </div>

@endsection
