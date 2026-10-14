<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StoreRepairTicketRequest;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('shared.repair-tickets.index', compact('repairTickets', 'search', 'status', 'statuses'));
    }

    public function create()
    {
        $assets = Asset::select('id', 'code', 'name')
            ->orderBy('code')->get();

        return view('shared.repair-tickets.create', compact('assets'));
    }

    public function store(StoreRepairTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $activeTicket = DB::transaction(function () use ($request, $validated) {
            $isHaveActiveTicket = RepairTicket::where('asset_id', $validated['asset_id'])
                ->whereIn('status', [
                    TicketStatus::PendingVerification->value,
                    TicketStatus::Verified->value,
                    TicketStatus::UnderRepair->value,
                ])
                ->lockForUpdate()
                ->first();

            if ($isHaveActiveTicket) {
                return $isHaveActiveTicket;
            }

            $photoPath = null;
            if ($request->hasFile('damage_photo')) {
                $photoPath = $request->file('damage_photo')->store('repair-tickets', 'public');
            }

            RepairTicket::create([
                'ticket_number' => RepairTicket::generateTicketNumber(),
                'asset_id' => $validated['asset_id'],
                'damage_description' => $validated['damage_description'],
                'damage_photo' => $photoPath,
                'reporter_id' => auth()->id(),
                'status' => TicketStatus::PendingVerification->value,
            ]);

            return null;
        });

        if ($activeTicket) {
            return back()->with(
                'error',
                "Aset ini sudah memiliki tiket perbaikan aktif (#{$activeTicket->ticket_number}) dengan status '{$activeTicket->status->label()}'."
            );
        }

        return redirect()->route('repair-tickets.index')
            ->with('success', 'Tiket perbaikan berhasil dibuat');
    }
}
