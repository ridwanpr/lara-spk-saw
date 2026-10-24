<?php

namespace App\Actions\RepairTickets;

use App\Enums\TicketStatus;
use App\Exceptions\ActiveRepairTicketException;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateRepairTicket
{
    /**
     * Create a new repair ticket.
     *
     * @param  array{asset_id: int, damage_description: string}  $data
     *
     * @throws ActiveRepairTicketException
     */
    public function create(User $reporter, array $data, ?UploadedFile $photo = null): RepairTicket
    {
        return DB::transaction(function () use ($reporter, $data, $photo): RepairTicket {
            $activeTicket = RepairTicket::where('asset_id', $data['asset_id'])
                ->whereIn('status', [
                    TicketStatus::PendingVerification->value,
                    TicketStatus::Verified->value,
                    TicketStatus::UnderRepair->value,
                ])
                ->lockForUpdate()
                ->first();

            if ($activeTicket instanceof RepairTicket) {
                throw new ActiveRepairTicketException($activeTicket);
            }

            $photoPath = $photo?->store('repair-tickets', 'public');

            return RepairTicket::create([
                'ticket_number' => RepairTicket::generateTicketNumber(),
                'asset_id' => $data['asset_id'],
                'damage_description' => $data['damage_description'],
                'damage_photo' => $photoPath,
                'reporter_id' => $reporter->id,
                'status' => TicketStatus::PendingVerification->value,
            ]);
        });
    }
}
