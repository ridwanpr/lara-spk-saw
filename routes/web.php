<?php

use App\Http\Controllers\Admin\AssetCategoryController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\RepairTicketController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubCriteriaController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::middleware('role:admin')->group(function (): void {
                Route::resource('users', UserController::class)->except(['show']);
                Route::resource('rooms', RoomController::class)->except(['show']);
                Route::resource('asset-categories', AssetCategoryController::class)->except(['show']);
                Route::resource('assets', AssetController::class)->except(['show']);
                Route::resource('criterias', CriteriaController::class)->except(['edit']);

                Route::prefix('criterias')
                    ->name('criterias.sub-criterias.')
                    ->group(function () {
                        Route::post('{criteria}/sub-criterias', [SubCriteriaController::class, 'store'])
                            ->name('store');
                        Route::put('sub-criterias/{subCriteria}', [SubCriteriaController::class, 'update'])
                            ->name('update');
                        Route::delete('sub-criterias/{subCriteria}', [SubCriteriaController::class, 'destroy'])
                            ->name('destroy');
                    });
            });
        });

    Route::middleware('role:admin,inspector,staff')
        ->prefix('repair-tickets')
        ->name('repair-tickets.')
        ->group(function () {
            Route::get('/', [RepairTicketController::class, 'index'])->name('index');
            Route::get('/create', [RepairTicketController::class, 'create'])->name('create');
            Route::post('/', [RepairTicketController::class, 'store'])->name('store');
        });
});
