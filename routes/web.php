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
use App\Http\Controllers\Backend\Configurations\GeneralController as ConfigGeneralController;
use App\Http\Controllers\Backend\Configurations\PointsController as ConfigPointsController;
use App\Http\Controllers\Backend\Configurations\RivalsController as ConfigRivalsController;

/*
 * ✅ Rutas para landing publica
*/
Route::get('/', function () {
    return view('backend.auth.login');
});

/*
 * ✅ Rutas publicas para maquetados
*/
Route::get('/templates/scouting-report', function () {
    return view('backend.templates.scouting-report');
})->name('templates.scouting-report');

Route::get('/templates/training-document', function () {
    return view('backend.templates.training-document');
})->name('templates.training-document');

Route::get('/templates/match-report', function () {
    return view('backend.templates.match-report');
})->name('templates.match-report');


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
        Route::get('/{id}/documents/download', 'downloadDocument')->name('documents.download');
        Route::get('/{id}/scouting-report', 'downloadScoutingReport')->name('scouting-report');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::post('/{id}/activate', 'activate')->name('activate');
        Route::post('/{id}/observations', 'storeObservation')->name('observations.store');
        Route::delete('/{id}/observations/{observationId}', 'destroyObservation')->name('observations.destroy');
        Route::post('/{id}/observations/{observationId}/activate', 'activateObservation')->name('observations.activate');
        Route::delete('/{id}/contacts/{contactId}', 'destroyContact')->name('contacts.destroy');
        Route::post('/{id}/contacts/{contactId}/activate', 'activateContact')->name('contacts.activate');
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
        Route::get('/{id}/view/report', 'viewReport')->name('view.report');
        Route::get('/{id}/view/team-photo', 'viewTeamPhoto')->name('view.team-photo');
        Route::get('/{id}/download/report', 'downloadReport')->name('download.report');
        Route::get('/{id}/download/team-photo', 'downloadTeamPhoto')->name('download.team-photo');
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
        Route::get('/{id}/view/document', 'viewDocument')->name('view.document');
        Route::get('/{id}/download/document', 'downloadDocument')->name('download.document');
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
    Route::prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [ConfigGeneralController::class, 'index'])->name('index');
        Route::put('/', [ConfigGeneralController::class, 'update'])->name('update');

        Route::prefix('rivals')->name('rivals.')->controller(ConfigRivalsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/new', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/activate', 'activate')->name('activate');
        });

        Route::prefix('points')->name('points.')->controller(ConfigPointsController::class)->group(function () {
            Route::get('/', 'index')->name('index');

            Route::get('/attack/new', 'createAttack')->name('attack.create');
            Route::post('/attack', 'storeAttack')->name('attack.store');
            Route::get('/attack/{id}/edit', 'editAttack')->name('attack.edit');
            Route::put('/attack/{id}', 'updateAttack')->name('attack.update');
            Route::delete('/attack/{id}', 'destroyAttack')->name('attack.destroy');
            Route::post('/attack/{id}/activate', 'activateAttack')->name('attack.activate');

            Route::get('/defensive/new', 'createDefensive')->name('defensive.create');
            Route::post('/defensive', 'storeDefensive')->name('defensive.store');
            Route::get('/defensive/{id}/edit', 'editDefensive')->name('defensive.edit');
            Route::put('/defensive/{id}', 'updateDefensive')->name('defensive.update');
            Route::delete('/defensive/{id}', 'destroyDefensive')->name('defensive.destroy');
            Route::post('/defensive/{id}/activate', 'activateDefensive')->name('defensive.activate');
        });
    });
    
});
