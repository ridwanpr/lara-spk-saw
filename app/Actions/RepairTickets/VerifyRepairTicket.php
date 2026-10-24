<?php

namespace App\Actions\RepairTickets;

use App\Enums\CriteriaInputType;
use App\Enums\TicketStatus;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\SubCriteria;
use App\Models\TicketEvaluation;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class VerifyRepairTicket
{
    /**
     * Verify and evaluate the given repair ticket.
     *
     * @param  array{estimated_cost: float|int|string, evaluations?: array<int, int|string>}  $data
     *
     * @throws DomainException
     */
    public function verify(RepairTicket $repairTicket, User $verifier, array $data): RepairTicket
    {
        if ($repairTicket->status !== TicketStatus::PendingVerification) {
            throw new DomainException('Tiket ini sudah diverifikasi sebelumnya atau statusnya tidak valid.');
        }

        DB::transaction(function () use ($repairTicket, $verifier, $data): void {
            $criterias = Criteria::with('subCriterias')->get()->keyBy('code');

            foreach ($data['evaluations'] ?? [] as $criteriaId => $value) {
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
                $cost = (float) $data['estimated_cost'];
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
                'estimated_cost' => $data['estimated_cost'],
                'verifier_id' => $verifier->id,
                'verified_at' => now(),
            ]);
        });

        return $repairTicket->refresh();
    }
}
