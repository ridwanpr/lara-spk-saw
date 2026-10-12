<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::resource('users', UserController::class)->except(['show']);
        });
});
