<?php

namespace App\Actions\RepairTickets;

use App\Enums\AssetCondition;
use App\Enums\TicketStatus;
use App\Models\RepairTicket;
use DomainException;
use Illuminate\Support\Facades\DB;

class StartRepairTicket
{
    /**
     * Transition a verified repair ticket into under_repair and update asset condition.
     *
     * @throws DomainException
     */
    public function start(RepairTicket $repairTicket): RepairTicket
    {
        if ($repairTicket->status !== TicketStatus::Verified) {
            throw new DomainException('Tiket harus berstatus terverifikasi untuk dapat memulai perbaikan.');
        }

        DB::transaction(function () use ($repairTicket): void {
            $repairTicket->update([
                'status' => TicketStatus::UnderRepair,
            ]);

            $repairTicket->asset?->update([
                'condition' => AssetCondition::UnderRepair,
            ]);
        });

        return $repairTicket->refresh();
    }
}
