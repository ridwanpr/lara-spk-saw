<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\Request;

class RepairTicketController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value();

        // Validate status against valid enum values to prevent invalid queries
        $selectedStatus = TicketStatus::tryFrom($status);

        $user = $request->user();

        $repairTickets = RepairTicket::with(['asset', 'reporter'])
            ->when($user instanceof User, function ($query) use ($user): void {
                $query->visibleTo($user);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ticket_number', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($assetQuery) use ($search): void {
                            $assetQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reporter', function ($reporterQuery) use ($search): void {
                            $reporterQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($selectedStatus !== null, function ($query) use ($selectedStatus): void {
                $query->where('status', $selectedStatus->value);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statuses = TicketStatus::cases();

        return view('admin.repair-tickets.index', compact('repairTickets', 'search', 'status', 'statuses'));
    }
}
