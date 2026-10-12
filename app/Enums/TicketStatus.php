<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case UnderRepair = 'under_repair';
    case Completed = 'completed';
    case Rejected = 'rejected';

    /**
     * Get the human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::UnderRepair => 'Sedang Diperbaiki',
            self::Completed => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }
}
