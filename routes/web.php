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
    
});



