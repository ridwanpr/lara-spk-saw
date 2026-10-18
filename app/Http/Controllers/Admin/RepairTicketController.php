<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RepairTickets\CalculateSawPriorities;
use App\Actions\RepairTickets\CreateRepairTicket;
use App\Actions\RepairTickets\VerifyRepairTicket;
use App\Enums\TicketStatus;
use App\Exceptions\ActiveRepairTicketException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StoreRepairTicketRequest;
use App\Http\Requests\Shared\VerifyRepairTicketRequest;
use App\Models\Asset;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class RepairTicketController extends Controller
{
    public function index(Request $request, CalculateSawPriorities $calculateSawPriorities): View
    {
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $selectedStatus = TicketStatus::tryFrom($status);
        $user = $request->user();

        $priorities = $calculateSawPriorities->calculate();

        $query = RepairTicket::with(['asset', 'reporter'])
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
            });

        // Jika difilter khusus status Verified, urutkan berdasarkan hasil ranking SAW tertinggi
        if ($selectedStatus === TicketStatus::Verified && ! empty($priorities)) {
            $allVerified = $query->get()->sortBy(function (RepairTicket $ticket) use ($priorities) {
                return $priorities[$ticket->id]['rank'] ?? 999999;
            })->values();

            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $repairTickets = new LengthAwarePaginator(
                $allVerified->forPage($currentPage, $perPage),
                $allVerified->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $repairTickets = $query->latest()->paginate(10)->withQueryString();
        }

        $statuses = TicketStatus::cases();

        return view('shared.repair-tickets.index', compact('repairTickets', 'search', 'status', 'statuses', 'priorities'));
    }

    public function create(): View
    {
        $assets = Asset::select('id', 'code', 'name')
            ->orderBy('code')->get();

        return view('shared.repair-tickets.create', compact('assets'));
    }

    public function store(StoreRepairTicketRequest $request, CreateRepairTicket $createRepairTicket): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $createRepairTicket->create(
                $user,
                $request->validated(),
                $request->file('damage_photo')
            );
        } catch (ActiveRepairTicketException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('repair-tickets.index')
            ->with('success', 'Tiket perbaikan berhasil dibuat');
    }

    public function show(string $ticketNumber, CalculateSawPriorities $calculateSawPriorities): View
    {
        $repairTicket = RepairTicket::with([
            'asset.room',
            'asset.category',
            'reporter',
            'verifier',
            'evaluations.criteria',
            'evaluations.subCriteria',
        ])
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        $criterias = Criteria::with('subCriterias')->orderBy('code')->get();
        $priorities = $calculateSawPriorities->calculate();
        $ticketPriority = $priorities[$repairTicket->id] ?? null;

        return view('shared.repair-tickets.show', compact('repairTicket', 'criterias', 'ticketPriority'));
    }

    public function verify(VerifyRepairTicketRequest $request, string|int $ticketId, VerifyRepairTicket $verifyRepairTicket): RedirectResponse
    {
        $repairTicket = RepairTicket::with(['asset.room.subCriteria'])->findOrFail($ticketId);

        if ($repairTicket->status !== TicketStatus::PendingVerification) {
            return back()->with('error', 'Tiket ini sudah diverifikasi sebelumnya atau statusnya tidak valid.');
        }

        /** @var User $verifier */
        $verifier = $request->user();

        $verifyRepairTicket->verify($repairTicket, $verifier, $request->validated());

        return redirect()
            ->route('repair-tickets.show', $repairTicket->ticket_number)
            ->with('success', 'Laporan tiket berhasil diverifikasi dan dievaluasi.');
    }
}
