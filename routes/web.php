<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\PlayersController;
use App\Http\Controllers\Backend\CategoriesController;
use App\Http\Controllers\Backend\TeamsController;
use App\Http\Controllers\Backend\MatchesController;
use App\Http\Controllers\Backend\TrainingsController;
use App\Http\Controllers\Backend\VenuesController;
use App\Http\Controllers\Backend\ConfigurationsController;

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
    Route::get('users/new', [UserController::class, 'create'])->name('users.new');
    Route::post('users/store', [UserController::class, 'store'])->name('users.store');
    Route::get('users/info/{id}', [UserController::class, 'info'])->name('users.info');
    Route::get('users/edit/{id}', [UserController::class, 'info'])->name('users.edit');
    Route::post('users/update/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');
    Route::post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');

    /*
     * ✅ Rutas CRUD para Jugadores / Players
    */
    Route::prefix('players')->name('players.')->controller(PlayersController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    /*
     * ✅ Rutas CRUD para Equipos / Teams
    */
    Route::prefix('teams')->name('teams.')->controller(TeamsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/activate', 'activate')->name('activate');
    });

    /*
     * ✅ Rutas CRUD para Partidos / Matches
    */
    Route::prefix('matches')->name('matches.')->controller(MatchesController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    /*
     * ✅ Rutas CRUD para Entrenamientos / Trainings
    */
    Route::prefix('trainings')->name('trainings.')->controller(TrainingsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    /*
     * ✅ Rutas CRUD para Sedes / Venues
    */
    Route::prefix('venues')->name('venues.')->controller(VenuesController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/activate', 'activate')->name('activate');
    });

    /*
     * ✅ Rutas para Configuración / Configurations
    */
    Route::prefix('configurations')->name('configurations.')->controller(ConfigurationsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });
    
});
