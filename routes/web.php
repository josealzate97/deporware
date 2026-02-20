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
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::view('/', 'backend.staff.index')->name('index');
        Route::view('/new', 'backend.staff.new')->name('new');
        Route::view('/{id}/edit', 'backend.staff.edit')->name('edit');
        Route::view('/{id}', 'backend.staff.show')->name('show');
    });

    Route::prefix('players')->name('players.')->group(function () {
        Route::view('/', 'backend.players.index')->name('index');
        Route::view('/new', 'backend.players.new')->name('new');
        Route::view('/{id}/edit', 'backend.players.edit')->name('edit');
        Route::view('/{id}', 'backend.players.show')->name('show');
    });

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::view('/', 'backend.categories.index')->name('index');
        Route::view('/new', 'backend.categories.new')->name('new');
        Route::view('/{id}/edit', 'backend.categories.edit')->name('edit');
        Route::view('/{id}', 'backend.categories.show')->name('show');
    });

    Route::prefix('teams')->name('teams.')->group(function () {
        Route::view('/', 'backend.teams.index')->name('index');
        Route::view('/new', 'backend.teams.new')->name('new');
        Route::view('/{id}/edit', 'backend.teams.edit')->name('edit');
        Route::view('/{id}', 'backend.teams.show')->name('show');
    });

    Route::prefix('matches')->name('matches.')->group(function () {
        Route::view('/', 'backend.matches.index')->name('index');
        Route::view('/new', 'backend.matches.new')->name('new');
        Route::view('/{id}/edit', 'backend.matches.edit')->name('edit');
        Route::view('/{id}', 'backend.matches.show')->name('show');
    });

    Route::prefix('trainings')->name('trainings.')->group(function () {
        Route::view('/', 'backend.trainings.index')->name('index');
        Route::view('/new', 'backend.trainings.new')->name('new');
        Route::view('/{id}/edit', 'backend.trainings.edit')->name('edit');
        Route::view('/{id}', 'backend.trainings.show')->name('show');
    });
    
});

