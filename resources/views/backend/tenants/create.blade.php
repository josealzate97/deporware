@extends('backend.layouts.main')

@section('title', 'Nueva Escuela')

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

                {{-- Preview del slug (solo informativo, se genera en el servidor) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted">Slug generado</label>
                    <div class="d-flex align-items-center gap-2">
                        <code id="slugPreview" class="px-3 py-2 rounded bg-light border text-secondary flex-grow-1" style="font-size:0.88rem">
                            se generará al guardar…
                        </code>
                        <span class="badge bg-light text-muted border" style="font-size:0.7rem;white-space:nowrap">Auto · inmutable</span>
                    </div>
                    <div class="form-text">Es el identificador de acceso de los usuarios de esta escuela. No se puede cambiar después.</div>
                </div>

                @push('scripts')
                <script>
                    (function () {
                        const inp  = document.getElementById('tenantName');
                        const prev = document.getElementById('slugPreview');
                        function toSlug(str) {
                            return str.toLowerCase()
                                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                                .replace(/[^a-z0-9]+/g, '_')
                                .replace(/^_+|_+$/g, '');
                        }
                        inp.addEventListener('input', function () {
                            const base = toSlug(this.value.trim());
                            prev.textContent = base ? base + '_XXX' : 'se generará al guardar…';
                        });
                    })();
                </script>
                @endpush

                <div class="mb-4">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check me-2"></i> Crear Escuela
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>

    </div>

@endsection
