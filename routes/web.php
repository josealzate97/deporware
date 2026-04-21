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
use App\Http\Controllers\Backend\TenantController;

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


/**
 *  ✅ Rutas para autenticacion
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

    /*
     * ✅ Dashboard — accesible para todos los roles
    */
    Route::get('home', [DefaultController::class, 'dashboard'])
    ->middleware('role:root,sport_manager,coordinator,coach')
    ->name('home');

    /*
     * ✅ Perfil propio — accesible para cualquier usuario autenticado
    */
    Route::get('profile', [UserController::class, 'profile'])->name('profile');

    /*
     * ✅ Zona Admin: Usuarios (Personal) y Sedes
     *    Solo Root y Gerente Deportivo
    */
    Route::middleware('role:root,sport_manager')->group(function () {

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/new', [UserController::class, 'create'])->name('users.new');
        Route::post('users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('users/info/{id}', [UserController::class, 'info'])->name('users.info');
        Route::get('users/edit/{id}', [UserController::class, 'info'])->name('users.edit');
        Route::post('users/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');
        Route::post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');

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

    });

    /*
     * ✅ Zona Operativa: Jugadores, Plantillas, Partidos, Entrenamientos
     *    Root, Gerente Deportivo, Coordinador y Entrenador
    */
    Route::middleware('role:root,sport_manager,coordinator,coach')->group(function () {

        /*
         * Jugadores / Players
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
         * Plantillas / Teams
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
         * Partidos / Matches
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
         * Entrenamientos / Trainings
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
            Route::post('/{id}/observations', 'storeObservation')->middleware('role:root,sport_manager,coordinator')->name('observations.store');
            Route::put('/{id}/observations/{observationId}', 'updateObservation')->middleware('role:root,sport_manager,coordinator')->name('observations.update');

        });

        /*
         * Configuraciones — entrada permitida a todos los roles.
         * Las restricciones de acción dentro del módulo se manejan con Gates.
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

    /*
     * ✅ Zona ROOT: Gestión de Escuelas (Tenants) y cambio de contexto
     *    Solo Root
    */
    Route::middleware('role:root')->group(function () {

        Route::prefix('tenants')->name('tenants.')->controller(TenantController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/new', 'create')->name('new');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/activate', 'activate')->name('activate');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::post('root/switch-tenant', [DefaultController::class, 'switchTenant'])->name('root.tenant.switch');
        Route::post('root/exit-tenant', [DefaultController::class, 'exitTenant'])->name('root.tenant.exit');

    });

});
