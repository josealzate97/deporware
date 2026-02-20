<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\UserController;

/*
 * ✅ Rutas para landing publica
*/
Route::get('/', function () {
    return view('backend.auth.login');
});


/**
 *  ✅ Rutas para autenticacion
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

     Route::get('home', [DefaultController::class, 'dashboard'])->name('home');

    /*
     * ✅ Rutas para usuarios
    */
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/info/{id}', [UserController::class, 'info'])->name('users.info');
    Route::post('users/update/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');
    Route::post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');

    /*
     * ✅ Rutas para vistas CRUD
    */
    Route::prefix('personal')->name('personal.')->group(function () {
        Route::view('/', 'backend.personal.index')->name('index');
        Route::view('/new', 'backend.personal.new')->name('new');
        Route::view('/{id}/edit', 'backend.personal.edit')->name('edit');
        Route::view('/{id}', 'backend.personal.show')->name('show');
    });

    Route::prefix('jugadores')->name('jugadores.')->group(function () {
        Route::view('/', 'backend.jugadores.index')->name('index');
        Route::view('/new', 'backend.jugadores.new')->name('new');
        Route::view('/{id}/edit', 'backend.jugadores.edit')->name('edit');
        Route::view('/{id}', 'backend.jugadores.show')->name('show');
    });

    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::view('/', 'backend.categorias.index')->name('index');
        Route::view('/new', 'backend.categorias.new')->name('new');
        Route::view('/{id}/edit', 'backend.categorias.edit')->name('edit');
        Route::view('/{id}', 'backend.categorias.show')->name('show');
    });

    Route::prefix('plantillas')->name('plantillas.')->group(function () {
        Route::view('/', 'backend.plantillas.index')->name('index');
        Route::view('/new', 'backend.plantillas.new')->name('new');
        Route::view('/{id}/edit', 'backend.plantillas.edit')->name('edit');
        Route::view('/{id}', 'backend.plantillas.show')->name('show');
    });

    Route::prefix('partidos')->name('partidos.')->group(function () {
        Route::view('/', 'backend.partidos.index')->name('index');
        Route::view('/new', 'backend.partidos.new')->name('new');
        Route::view('/{id}/edit', 'backend.partidos.edit')->name('edit');
        Route::view('/{id}', 'backend.partidos.show')->name('show');
    });

    Route::prefix('entrenamientos')->name('entrenamientos.')->group(function () {
        Route::view('/', 'backend.entrenamientos.index')->name('index');
        Route::view('/new', 'backend.entrenamientos.new')->name('new');
        Route::view('/{id}/edit', 'backend.entrenamientos.edit')->name('edit');
        Route::view('/{id}', 'backend.entrenamientos.show')->name('show');
    });
    
});


