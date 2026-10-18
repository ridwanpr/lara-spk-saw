<?php

namespace App\Actions\RepairTickets;

use App\Enums\CriteriaType;
use App\Enums\TicketStatus;
use App\Models\Criteria;
use App\Models\RepairTicket;

class CalculateSawPriorities
{
    /**
     * Hitung skor dan urutan prioritas SAW untuk seluruh tiket berstatus Verified.
     *
     * @return array<int, array{score: float, rank: int, total: int}>
     */
    public function calculate(): array
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

