<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route(auth()->check() ? 'tasks.index' : 'login'));

Route::middleware('guest')->group(function () {
    Route::view('/iniciar-sesion', 'auth.access', ['registering' => false])->name('login');
    Route::view('/crear-cuenta', 'auth.access', ['registering' => true])->name('register');
    Route::post('/iniciar-sesion', [AccountController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::post('/crear-cuenta', [AccountController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::resource('tareas', TaskController::class)->parameters(['tareas' => 'task'])->names('tasks');
    Route::patch('/tareas/{task}/estado', [TaskController::class, 'completion'])->name('tasks.completion');
    Route::post('/tareas/{task}/sugerencias', [TaskController::class, 'suggest'])->middleware('throttle:6,1')->name('tasks.suggest');

    Route::view('/mi-cuenta', 'account.show')->name('account.show');
    Route::post('/cerrar-sesion', [AccountController::class, 'logout'])->name('logout');
    Route::delete('/mi-cuenta', [AccountController::class, 'destroy'])->name('account.destroy');
});
