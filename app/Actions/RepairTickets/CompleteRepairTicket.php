<?php

namespace App\Actions\RepairTickets;

use App\Enums\AssetCondition;
use App\Enums\TicketStatus;
use App\Models\RepairTicket;
use DomainException;
use Illuminate\Support\Facades\DB;

class CompleteRepairTicket
{
    /**
     * Transition an under-repair ticket into completed and record the actual repair outcome.
     *
     * @param  array{actual_cost: float|int|string, completion_notes?: string|null}  $data
     *
     * @throws DomainException
     */
    public function complete(RepairTicket $repairTicket, array $data): RepairTicket
    {
        if ($repairTicket->status !== TicketStatus::UnderRepair) {
            throw new DomainException('Tiket harus berstatus sedang diperbaiki untuk dapat ditandai selesai.');
        }

        DB::transaction(function () use ($repairTicket, $data): void {
            $repairTicket->update([
                'status' => TicketStatus::Completed,
                'actual_cost' => $data['actual_cost'],
                'completion_notes' => $data['completion_notes'] ?? null,
            ]);

            $repairTicket->asset?->update([
                'condition' => AssetCondition::Good,
            ]);
        });

        return $repairTicket->refresh();
    }
}
