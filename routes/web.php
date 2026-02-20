<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\StaffController;
use App\Http\Controllers\Backend\PlayersController;
use App\Http\Controllers\Backend\CategoriesController;
use App\Http\Controllers\Backend\TeamsController;
use App\Http\Controllers\Backend\MatchesController;
use App\Http\Controllers\Backend\TrainingsController;

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
     * ✅ Rutas CRUD para Personal / Staff
     * 
    */
    Route::prefix('staff')->name('staff.')->controller(StaffController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

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
     * ✅ Rutas CRUD para Categorias / Categories
    */
    Route::prefix('categories')->name('categories.')->controller(CategoriesController::class)->group(function () {
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
    
});
