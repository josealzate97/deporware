@extends('backend.layouts.main')

@section('title', 'Editar Escuela')

@section('content')

    <div class="container-fluid p-4">

        @push('breadcrumb')
            @include('backend.components.breadcrumb', [
                'section' => ['route' => 'tenants.index', 'icon' => 'fa-solid fa-building', 'label' => 'Escuelas'],
                'page'    => ['label' => 'Editar Escuela']
            ])
        @endpush

        <div class="card p-4 section-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="section-hero-icon">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0">{{ $tenant->name }}</h2>
                    <div class="text-muted small fw-bold">Edita los datos de esta escuela</div>
                </div>
            </div>
        </div>

        <div class="card p-4" style="max-width:560px">
            <form method="POST" action="{{ route('tenants.update', $tenant->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $tenant->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Slug: solo lectura. Es el login identifier del tenant --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold d-flex align-items-center gap-2">
                        Slug de acceso
                        <span class="badge bg-warning text-dark" style="font-size:0.68rem">Inmutable</span>
                    </label>
                    <div class="input-group">
                        <code id="slugValue" class="form-control bg-light text-secondary" style="font-size:0.9rem">{{ $tenant->slug }}</code>
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $tenant->slug }}').then(() => { this.innerHTML='<i class=\'fas fa-check\'></i>'; setTimeout(() => this.innerHTML='<i class=\'fas fa-copy\'></i>', 1500); })" title="Copiar">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="form-text">Los usuarios de esta escuela lo usan para iniciar sesión. Si necesitas cambiarlo contacta al super admin.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" {{ (string) old('status', $tenant->status) === '1' ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ (string) old('status', $tenant->status) === '0' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 p-3 rounded" style="background:var(--surface-2, #f5f7fa)">
                    <div class="small text-muted"><span class="fw-semibold">ID:</span> <code>{{ $tenant->id }}</code></div>
                    <div class="small text-muted mt-1"><span class="fw-semibold">Creada:</span> {{ $tenant->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i> Guardar cambios
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>

    </div>

@endsection
