<?php

use App\Enums\TicketStatus;

test('status colors are mutually distinct and match the badge palette', function (): void {
    expect(TicketStatus::PendingVerification->color())->toBe('secondary')
        ->and(TicketStatus::Verified->color())->toBe('info')
        ->and(TicketStatus::UnderRepair->color())->toBe('warning')
        ->and(TicketStatus::Completed->color())->toBe('success')
        ->and(TicketStatus::Rejected->color())->toBe('danger');

    $colors = collect(TicketStatus::cases())
        ->map(fn (TicketStatus $status): string => $status->color())
        ->unique();

    expect($colors)->toHaveCount(count(TicketStatus::cases()));
});
