<?php

namespace App\Actions\RepairTickets;

use App\Enums\TicketStatus;
use App\Models\RepairTicket;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RejectRepairTicket
{
    /**
     * Reject a pending repair ticket and store the rejection reason.
     *
     * @throws DomainException
     */
    public function reject(RepairTicket $repairTicket, User $rejector, string $rejectionReason): RepairTicket
    {
        if ($repairTicket->status !== TicketStatus::PendingVerification) {
            throw new DomainException('Tiket ini sudah diverifikasi sebelumnya atau statusnya tidak valid.');
        }

        DB::transaction(function () use ($repairTicket, $rejector, $rejectionReason): void {
            $repairTicket->update([
                'status' => TicketStatus::Rejected,
                'verifier_id' => $rejector->id,
                'verified_at' => now(),
                'completion_notes' => $rejectionReason,
            ]);
        });

        return $repairTicket->refresh();
    }
}
