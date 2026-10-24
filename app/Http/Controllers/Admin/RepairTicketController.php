<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RepairTickets\CalculateSawPriorities;
use App\Actions\RepairTickets\CompleteRepairTicket;
use App\Actions\RepairTickets\CreateRepairTicket;
use App\Actions\RepairTickets\RejectRepairTicket;
use App\Actions\RepairTickets\StartRepairTicket;
use App\Actions\RepairTickets\VerifyRepairTicket;
use App\Enums\TicketStatus;
use App\Exceptions\ActiveRepairTicketException;
use App\Exports\RepairTicketsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\CompleteRepairTicketRequest;
use App\Http\Requests\Shared\ExportRepairTicketsRequest;
use App\Http\Requests\Shared\RejectRepairTicketRequest;
use App\Http\Requests\Shared\StartRepairTicketRequest;
use App\Http\Requests\Shared\StoreRepairTicketRequest;
use App\Http\Requests\Shared\VerifyRepairTicketRequest;
use App\Models\Asset;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function export(ExportRepairTicketsRequest $request, CalculateSawPriorities $calculateSawPriorities): BinaryFileResponse|Response
    {
        $validated = $request->validated();
        $format = $validated['format'];
        $status = $validated['status'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'latest';

        $user = $request->user();
        $selectedStatus = $status !== null ? TicketStatus::tryFrom($status) : null;
        $priorities = $calculateSawPriorities->calculate();

        $query = RepairTicket::with(['asset', 'reporter', 'verifier'])
            ->when($user instanceof User, function ($query) use ($user): void {
                $query->visibleTo($user);
            })
            ->when($selectedStatus !== null, function ($query) use ($selectedStatus): void {
                $query->where('status', $selectedStatus->value);
            })
            ->when($startDate !== null, function ($query) use ($startDate): void {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate !== null, function ($query) use ($endDate): void {
                $query->whereDate('created_at', '<=', $endDate);
            });

        if ($sortBy === 'priority') {
            $tickets = $query->get()->sortBy(function (RepairTicket $ticket) use ($priorities) {
                return $priorities[$ticket->id]['rank'] ?? 999999;
            })->values();
        } else {
            $tickets = $query->latest()->get();
        }

        // Attach calculated SAW rank & score to ticket models
        $tickets->each(function (RepairTicket $ticket) use ($priorities): void {
            $ticket->saw_rank = $priorities[$ticket->id]['rank'] ?? null;
            $ticket->saw_score = $priorities[$ticket->id]['score'] ?? null;
        });

        $timestamp = now()->format('Ymd_His');

        if ($format === 'excel') {
            $fileName = "laporan_tiket_perbaikan_{$timestamp}.xlsx";

            return Excel::download(new RepairTicketsExport($tickets), $fileName);
        }

        $filters = [
            'status_label' => $selectedStatus?->label() ?? 'Semua Status',
            'sort_label' => $sortBy === 'priority' ? 'Prioritas SAW (Skor Tertinggi)' : 'Tanggal Terbaru',
            'date_range' => ($startDate && $endDate)
                ? "{$startDate} s/d {$endDate}"
                : ($startDate ? "Mulai {$startDate}" : ($endDate ? "Hingga {$endDate}" : 'Semua Tanggal')),
        ];

        $fileName = "laporan_tiket_perbaikan_{$timestamp}.pdf";
        $pdf = Pdf::loadView('shared.repair-tickets.exports.pdf', compact('tickets', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function create(): View
    {
        $assets = Asset::with(['activeRepairTicket'])
            ->orderBy('code')
            ->get();

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

    public function startRepair(StartRepairTicketRequest $request, string|int $ticketId, StartRepairTicket $startRepairTicket): RedirectResponse
    {
        $repairTicket = RepairTicket::with('asset')->findOrFail($ticketId);

        try {
            $startRepairTicket->start($repairTicket);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('repair-tickets.show', $repairTicket->ticket_number)
            ->with('success', 'Proses perbaikan berhasil dimulai.');
    }

    public function completeRepair(CompleteRepairTicketRequest $request, string|int $ticketId, CompleteRepairTicket $completeRepairTicket): RedirectResponse
    {
        $repairTicket = RepairTicket::with('asset')->findOrFail($ticketId);

        try {
            $completeRepairTicket->complete($repairTicket, $request->validated());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('repair-tickets.show', $repairTicket->ticket_number)
            ->with('success', 'Perbaikan berhasil ditandai selesai.');
    }

    public function reject(RejectRepairTicketRequest $request, string|int $ticketId, RejectRepairTicket $rejectRepairTicket): RedirectResponse
    {
        $repairTicket = RepairTicket::with('asset')->findOrFail($ticketId);

        /** @var User $rejector */
        $rejector = $request->user();

        try {
            $rejectRepairTicket->reject($repairTicket, $rejector, $request->validated('rejection_reason'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('repair-tickets.show', $repairTicket->ticket_number)
            ->with('success', 'Laporan tiket berhasil ditolak.');
    }
}
