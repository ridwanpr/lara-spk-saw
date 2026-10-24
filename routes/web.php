<?php

use App\Actions\RepairTickets\CalculateSawPriorities;
use App\Enums\TicketStatus;
use App\Http\Controllers\Admin\AssetCategoryController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\RepairTicketController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubCriteriaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DocumentationController;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', function (CalculateSawPriorities $calculateSawPriorities) {
        $user = auth()->user();
        $ticketQuery = RepairTicket::with(['asset', 'reporter'])
            ->when($user instanceof User, fn ($query) => $query->visibleTo($user));

        $stats = [
            'total' => (clone $ticketQuery)->count(),
            'pending' => (clone $ticketQuery)->where('status', TicketStatus::PendingVerification)->count(),
            'verified' => (clone $ticketQuery)->where('status', TicketStatus::Verified)->count(),
            'under_repair' => (clone $ticketQuery)->where('status', TicketStatus::UnderRepair)->count(),
            'completed' => (clone $ticketQuery)->where('status', TicketStatus::Completed)->count(),
            'total_assets' => Asset::count(),
        ];

        $priorities = $calculateSawPriorities->calculate();

        $topPriorities = RepairTicket::with(['asset', 'asset.room', 'reporter'])
            ->where('status', TicketStatus::Verified)
            ->when($user instanceof User, fn ($query) => $query->visibleTo($user))
            ->get()
            ->sortBy(fn (RepairTicket $ticket) => $priorities[$ticket->id]['rank'] ?? 999999)
            ->take(5);

        return view('welcome', compact('stats', 'topPriorities', 'priorities'));
    })->name('dashboard');

    Route::get('/documentation', DocumentationController::class)->name('documentation');

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
            Route::get('/export', [RepairTicketController::class, 'export'])->name('export');
            Route::get('/create', [RepairTicketController::class, 'create'])->name('create');
            Route::post('/', [RepairTicketController::class, 'store'])->name('store');
            Route::get('/{ticketNumber}', [RepairTicketController::class, 'show'])->name('show');

            Route::post('/verify/{ticketId}', [RepairTicketController::class, 'verify'])->name('verify');
            Route::post('/{ticketId}/start-repair', [RepairTicketController::class, 'startRepair'])->name('start-repair');
            Route::post('/{ticketId}/complete-repair', [RepairTicketController::class, 'completeRepair'])->name('complete-repair');
            Route::post('/{ticketId}/reject', [RepairTicketController::class, 'reject'])->name('reject');
        });
});
