<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StoreRepairTicketRequest;
use App\Http\Requests\Shared\VerifyRepairTicketRequest;
use App\Models\Asset;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\SubCriteria;
use App\Models\TicketEvaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RepairTicketController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $selectedStatus = TicketStatus::tryFrom($status);
        $user = $request->user();

        $priorities = $this->calculateSawPriorities();

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

    public function show(string $ticketNumber)
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
        $priorities = $this->calculateSawPriorities();
        $ticketPriority = $priorities[$repairTicket->id] ?? null;

        return view('shared.repair-tickets.show', compact('repairTicket', 'criterias', 'ticketPriority'));
    }

    public function verify(VerifyRepairTicketRequest $request, string|int $ticketId): RedirectResponse
    {
        $repairTicket = RepairTicket::with(['asset.room.subCriteria'])->findOrFail($ticketId);

        if ($repairTicket->status !== TicketStatus::PendingVerification) {
            return back()->with('error', 'Tiket ini sudah diverifikasi sebelumnya atau statusnya tidak valid.');
        }

        DB::transaction(function () use ($request, $repairTicket): void {
            $criterias = Criteria::with('subCriterias')->get()->keyBy('code');

            foreach ($request->input('evaluations', []) as $criteriaId => $value) {
                $criteria = Criteria::find($criteriaId);
                if (! $criteria) {
                    continue;
                }

                $subCriteria = $criteria->input_type === CriteriaInputType::Categorical
                    ? SubCriteria::find($value)
                    : SubCriteria::where('criteria_id', $criteria->id)
                        ->where('min_value', '<=', $value)
                        ->where('max_value', '>=', $value)
                        ->first();

                if ($subCriteria) {
                    TicketEvaluation::updateOrCreate(
                        ['ticket_id' => $repairTicket->id, 'criteria_id' => $criteria->id],
                        ['sub_criteria_id' => $subCriteria->id, 'score' => $subCriteria->crisp_value ?? 0]
                    );
                }
            }

            if ($c2 = $criterias->get('C2')) {
                $cost = (float) $request->input('estimated_cost');
                $subCriteria = SubCriteria::where('criteria_id', $c2->id)
                    ->where('min_value', '<=', $cost)
                    ->where('max_value', '>=', $cost)
                    ->first();

                if ($subCriteria) {
                    TicketEvaluation::updateOrCreate(
                        ['ticket_id' => $repairTicket->id, 'criteria_id' => $c2->id],
                        ['sub_criteria_id' => $subCriteria->id, 'score' => $subCriteria->crisp_value ?? 0]
                    );
                }
            }

            if ($c3 = $criterias->get('C3')) {
                $roomSub = $repairTicket->asset->room?->subCriteria;

                if ($roomSub) {
                    TicketEvaluation::updateOrCreate(
                        ['ticket_id' => $repairTicket->id, 'criteria_id' => $c3->id],
                        ['sub_criteria_id' => $roomSub->id, 'score' => $roomSub->crisp_value ?? 0]
                    );
                }
            }

            if ($c4 = $criterias->get('C4')) {
                $age = max(0, now()->year - (int) $repairTicket->asset->procurement_year);
                $subCriteria = SubCriteria::where('criteria_id', $c4->id)
                    ->where('min_value', '<=', $age)
                    ->where('max_value', '>=', $age)
                    ->first();

                if ($subCriteria) {
                    TicketEvaluation::updateOrCreate(
                        ['ticket_id' => $repairTicket->id, 'criteria_id' => $c4->id],
                        ['sub_criteria_id' => $subCriteria->id, 'score' => $subCriteria->crisp_value ?? 0]
                    );
                }
            }

            $repairTicket->update([
                'status' => TicketStatus::Verified,
                'estimated_cost' => $request->input('estimated_cost'),
                'verifier_id' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('repair-tickets.show', $repairTicket->ticket_number)
            ->with('success', 'Laporan tiket berhasil diverifikasi dan dievaluasi.');
    }

    /**
     * Hitung skor dan urutan prioritas SAW untuk seluruh tiket berstatus Verified.
     *
     * @return array<int, array{score: float, rank: int, total: int}>
     */
    private function calculateSawPriorities(): array
    {
        $tickets = RepairTicket::with('evaluations')
            ->where('status', TicketStatus::Verified)
            ->get();

        if ($tickets->isEmpty()) {
            return [];
        }

        $criterias = Criteria::all();
        $totalWeight = (float) $criterias->sum('weight');

        if ($totalWeight <= 0) {
            return [];
        }

        // Cari pembagi max (benefit) dan min (cost) tiap kriteria
        $divisors = [];
        foreach ($criterias as $criteria) {
            $scores = $tickets->map(function (RepairTicket $ticket) use ($criteria) {
                return (float) ($ticket->evaluations->firstWhere('criteria_id', $criteria->id)?->score ?? 0);
            })->filter(fn (float $val) => $val > 0);

            if ($scores->isEmpty()) {
                $divisors[$criteria->id] = 0.0;

                continue;
            }

            $divisors[$criteria->id] = $criteria->type === CriteriaType::Benefit
                ? (float) $scores->max()
                : (float) $scores->min();
        }

        // Hitung nilai preferensi (V) tiap tiket dengan bobot ternormalisasi
        $results = [];
        foreach ($tickets as $ticket) {
            $preferenceValue = 0.0;

            foreach ($criterias as $criteria) {
                $score = (float) ($ticket->evaluations->firstWhere('criteria_id', $criteria->id)?->score ?? 0);
                $divisor = $divisors[$criteria->id] ?? 0.0;

                if ($score <= 0 || $divisor <= 0) {
                    continue;
                }

                $normalizedMatrix = $criteria->type === CriteriaType::Benefit
                    ? ($score / $divisor)
                    : ($divisor / $score);

                $normalizedWeight = ((float) $criteria->weight) / $totalWeight;
                $preferenceValue += $normalizedMatrix * $normalizedWeight;
            }

            $results[$ticket->id] = [
                'score' => round($preferenceValue, 4),
                'created_at' => $ticket->created_at?->timestamp ?? 0,
            ];
        }

        // Urutkan dari skor tertinggi; jika seri, dahulukan tiket yang dilaporkan lebih awal
        uasort($results, function (array $a, array $b): int {
            if ($a['score'] === $b['score']) {
                return $a['created_at'] <=> $b['created_at'];
            }

            return $b['score'] <=> $a['score'];
        });

        $ranked = [];
        $rank = 1;
        $total = count($results);

        foreach ($results as $ticketId => $data) {
            $ranked[$ticketId] = [
                'score' => $data['score'],
                'rank' => $rank++,
                'total' => $total,
            ];
        }

        return $ranked;
    }
}
