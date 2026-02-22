@extends('backend.layouts.guest')

@section('title', 'Login')
@section('meta_description', 'Inicia sesión en deporware para gestionar inventario, ventas y reportes.')

@push('scripts')
    @vite(['resources/js/modules/auth.js'])
@endpush

@section('content')

    <div class="login-split">

        <div class="login-panel login-panel--brand">

            <div class="brand-content">

                <h1 class="brand-title">
                    <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo deporware" class="brand-title-logo">
                    <span>Deporware</span>
                </h1>
                <p>Controla inventario deportivo, ventas y operación diaria de tus sedes en un solo lugar.</p>

                <div class="brand-stats">
                    <div>
                        <span>Gestión</span>
                        <strong>Clubes y centros</strong>
                    </div>
                    <div>
                        <span>Seguimiento</span>
                        <strong>Estado en tiempo real</strong>
                    </div>
                </div>

            </div>

        </div>

        <div class="login-panel login-panel--form">

            <div class="login-card">

                <div class="login-card__brand">
                    <div class="login-hero-icon login-hero-icon--sm">
                        <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo deporware" class="login-card-logo">
                    </div>
                    <div>
                        <h2>Bienvenido de nuevo</h2>
                        <p>Accede para gestionar tu operación deportiva</p>
                    </div>
                </div>

                <form id="loginForm" method="POST" action="{{ route('login') }}" class="login-form">
                    
                    @csrf

                    <div class="login-field">
                        <label for="username" class="form-label">Usuario</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" required aria-label="Usuario">
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required aria-label="Contraseña">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        Iniciar sesión
                    </button>
                </form>

            @if ($errors->any())

                <div class="login-alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>
                        <strong>Ups, algo salió mal</strong>
                        <span>Usuario o contraseña incorrectos.</span>
                    </div>
                </div>

            @endif
        </div>

    </div>

@endsection
