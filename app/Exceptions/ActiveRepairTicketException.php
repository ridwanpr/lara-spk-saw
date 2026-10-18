<?php

namespace App\Exceptions;

use App\Models\RepairTicket;
use Exception;

class ActiveRepairTicketException extends Exception
{
    public function __construct(
        public readonly RepairTicket $activeTicket,
        ?string $message = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        $resolvedMessage = $message ?? "Aset ini sudah memiliki tiket perbaikan aktif (#{$this->activeTicket->ticket_number}) dengan status '{$this->activeTicket->status->label()}'.";

        parent::__construct($resolvedMessage, $code, $previous);
    }
}

