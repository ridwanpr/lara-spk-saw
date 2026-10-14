<?php

use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role access for Repair Tickets index endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $this->get(route('admin.repair-tickets.index'))
            ->assertRedirect(route('login'));
    });

    test('admin can access repair tickets index endpoint', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index'))
            ->assertOk();
    });

    test('inspector can access repair tickets index endpoint', function (): void {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)
            ->get(route('admin.repair-tickets.index'))
            ->assertOk();
    });

    test('staff can access repair tickets index endpoint', function (): void {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('admin.repair-tickets.index'))
            ->assertOk();
    });
});

describe('role-based ticket visibility and scoping', function (): void {
    test('staff only sees their own reported tickets and cannot see others', function (): void {
        $staffA = User::factory()->staff()->create(['name' => 'Guru IPA']);
        $staffB = User::factory()->staff()->create(['name' => 'Guru IPS']);

        $ticketA = RepairTicket::factory()->create([
            'reporter_id' => $staffA->id,
            'ticket_number' => 'TCK-STAFF-A',
        ]);
        $ticketB = RepairTicket::factory()->create([
            'reporter_id' => $staffB->id,
            'ticket_number' => 'TCK-STAFF-B',
        ]);

        $response = $this->actingAs($staffA)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertDontSeeText($ticketB->ticket_number);

        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');
        expect($tickets->pluck('id'))->toContain($ticketA->id)
            ->and($tickets->pluck('id'))->not->toContain($ticketB->id);
    });

    test('admin can see tickets reported by any user', function (): void {
        $admin = User::factory()->admin()->create();
        $staffA = User::factory()->staff()->create();
        $staffB = User::factory()->staff()->create();

        $ticketA = RepairTicket::factory()->create(['reporter_id' => $staffA->id]);
        $ticketB = RepairTicket::factory()->create(['reporter_id' => $staffB->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText($ticketB->ticket_number);

        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');
        expect($tickets->pluck('id'))->toContain($ticketA->id, $ticketB->id);
    });

    test('inspector can see tickets reported by any user', function (): void {
        $inspector = User::factory()->inspector()->create();
        $staffA = User::factory()->staff()->create();
        $staffB = User::factory()->staff()->create();

        $ticketA = RepairTicket::factory()->create(['reporter_id' => $staffA->id]);
        $ticketB = RepairTicket::factory()->create(['reporter_id' => $staffB->id]);

        $response = $this->actingAs($inspector)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText($ticketB->ticket_number);

        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');
        expect($tickets->pluck('id'))->toContain($ticketA->id, $ticketB->id);
    });

    test('visibleTo model scope scopes correctly for staff and does not filter for admin or inspector', function (): void {
        $staff = User::factory()->staff()->create();
        $otherStaff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();

        $staffTicket = RepairTicket::factory()->create(['reporter_id' => $staff->id]);
        $otherTicket = RepairTicket::factory()->create(['reporter_id' => $otherStaff->id]);

        expect(RepairTicket::visibleTo($staff)->pluck('id'))
            ->toContain($staffTicket->id)
            ->not->toContain($otherTicket->id)
            ->and(RepairTicket::visibleTo($admin)->pluck('id'))
            ->toContain($staffTicket->id, $otherTicket->id)
            ->and(RepairTicket::visibleTo($inspector)->pluck('id'))
            ->toContain($staffTicket->id, $otherTicket->id)
            ->and(RepairTicket::forUser($staff)->pluck('id'))
            ->toContain($staffTicket->id)
            ->not->toContain($otherTicket->id);
    });
});

describe('repair tickets listing (index)', function (): void {
    test('admin can view repair tickets list with correct view and view data', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $response->assertViewIs('admin.repair-tickets.index');
        $response->assertViewHasAll(['repairTickets', 'search', 'status', 'statuses']);
        $response->assertSeeText($ticket->ticket_number);
        $response->assertSeeText($ticket->asset->name);
        $response->assertSeeText($ticket->asset->code);
        $response->assertSeeText($ticket->reporter->name);
        $response->assertSeeText($ticket->status->label());
    });

    test('displays empty state when no repair tickets exist', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $response->assertSeeText('Data tiket tidak ditemukan');
        $response->assertSeeText('Belum ada laporan perbaikan aset yang masuk.');
    });
});

describe('repair tickets search filtering', function (): void {
    test('filters repair tickets by ticket number', function (): void {
        $admin = User::factory()->admin()->create();
        $matching = RepairTicket::factory()->create(['ticket_number' => 'TCK-20261014-9999']);
        $other = RepairTicket::factory()->create(['ticket_number' => 'TCK-20261014-1111']);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => '9999']));

        $response->assertOk();
        $response->assertSeeText($matching->ticket_number);
        $response->assertDontSeeText($other->ticket_number);
    });

    test('filters repair tickets by asset name', function (): void {
        $admin = User::factory()->admin()->create();
        $assetA = Asset::factory()->create(['name' => 'Projector Epson EB-X400']);
        $assetB = Asset::factory()->create(['name' => 'Printer Canon Pixma']);

        $ticketA = RepairTicket::factory()->create(['asset_id' => $assetA->id]);
        $ticketB = RepairTicket::factory()->create(['asset_id' => $assetB->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => 'Projector']));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText('Projector Epson EB-X400');
        $response->assertDontSeeText($ticketB->ticket_number);
    });

    test('filters repair tickets by asset code', function (): void {
        $admin = User::factory()->admin()->create();
        $assetA = Asset::factory()->create(['code' => 'AST-PROJ-01']);
        $assetB = Asset::factory()->create(['code' => 'AST-PRIN-02']);

        $ticketA = RepairTicket::factory()->create(['asset_id' => $assetA->id]);
        $ticketB = RepairTicket::factory()->create(['asset_id' => $assetB->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => 'PROJ-01']));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText('AST-PROJ-01');
        $response->assertDontSeeText($ticketB->ticket_number);
    });

    test('filters repair tickets by reporter name', function (): void {
        $admin = User::factory()->admin()->create();
        $reporterA = User::factory()->staff()->create(['name' => 'Budi Santoso']);
        $reporterB = User::factory()->staff()->create(['name' => 'Siti Nurhaliza']);

        $ticketA = RepairTicket::factory()->create(['reporter_id' => $reporterA->id]);
        $ticketB = RepairTicket::factory()->create(['reporter_id' => $reporterB->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => 'Budi']));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText('Budi Santoso');
        $response->assertDontSeeText($ticketB->ticket_number);
    });

    test('displays empty search message when search matches no ticket', function (): void {
        $admin = User::factory()->admin()->create();
        RepairTicket::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => 'nonexistentquery12345']));

        $response->assertOk();
        $response->assertSeeText('Data tiket tidak ditemukan');
        $response->assertSeeText('Tidak ada tiket yang cocok dengan kriteria filter.');
    });

    test('staff searching tickets cannot see other staff tickets even if they match query', function (): void {
        $staff = User::factory()->staff()->create(['name' => 'Ahmad Staff']);
        $otherStaff = User::factory()->staff()->create(['name' => 'Citra Staff']);

        $asset = Asset::factory()->create(['name' => 'Shared Laptop Acer']);

        $staffTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'reporter_id' => $staff->id,
            'ticket_number' => 'TCK-MY-LAPTOP',
        ]);

        $otherTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'reporter_id' => $otherStaff->id,
            'ticket_number' => 'TCK-OTHER-LAPTOP',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('admin.repair-tickets.index', ['search' => 'Laptop']));

        $response->assertOk();
        $response->assertSeeText($staffTicket->ticket_number);
        $response->assertDontSeeText($otherTicket->ticket_number);
    });
});

describe('repair tickets status filtering', function (): void {
    test('filters repair tickets by status', function (): void {
        $admin = User::factory()->admin()->create();
        $pendingTicket = RepairTicket::factory()->pendingVerification()->create();
        $completedTicket = RepairTicket::factory()->completed()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['status' => TicketStatus::PendingVerification->value]));

        $response->assertOk();
        $response->assertSeeText($pendingTicket->ticket_number);
        $response->assertDontSeeText($completedTicket->ticket_number);
    });

    test('gracefully ignores invalid status value and lists all tickets', function (): void {
        $admin = User::factory()->admin()->create();
        $ticketA = RepairTicket::factory()->pendingVerification()->create();
        $ticketB = RepairTicket::factory()->completed()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['status' => 'invalid_status_value']));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText($ticketB->ticket_number);
    });
});

describe('repair tickets combined filtering', function (): void {
    test('filters repair tickets by both search and status simultaneously', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create(['name' => 'Server Dell PowerEdge']);

        $targetTicket = RepairTicket::factory()
            ->pendingVerification()
            ->create([
                'asset_id' => $asset->id,
                'ticket_number' => 'TCK-TARGET-001',
            ]);

        $sameStatusDiffSearch = RepairTicket::factory()
            ->pendingVerification()
            ->create([
                'ticket_number' => 'TCK-OTHER-002',
            ]);

        $sameSearchDiffStatus = RepairTicket::factory()
            ->completed()
            ->create([
                'asset_id' => $asset->id,
                'ticket_number' => 'TCK-COMPLETED-003',
            ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', [
                'search' => 'PowerEdge',
                'status' => TicketStatus::PendingVerification->value,
            ]));

        $response->assertOk();
        $response->assertSeeText($targetTicket->ticket_number);
        $response->assertDontSeeText($sameStatusDiffSearch->ticket_number);
        $response->assertDontSeeText($sameSearchDiffStatus->ticket_number);
    });
});

describe('repair tickets ordering and pagination', function (): void {
    test('orders repair tickets with latest created first', function (): void {
        $admin = User::factory()->admin()->create();
        $oldTicket = RepairTicket::factory()->create(['created_at' => now()->subDays(2)]);
        $newTicket = RepairTicket::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index'));

        $response->assertOk();
        $tickets = $response->viewData('repairTickets');
        expect($tickets->first()->id)->toBe($newTicket->id)
            ->and($tickets->last()->id)->toBe($oldTicket->id);
    });

    test('paginates repair tickets 10 items per page and preserves query strings', function (): void {
        $admin = User::factory()->admin()->create();
        RepairTicket::factory()->count(15)->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.repair-tickets.index', ['search' => 'TCK']));

        $response->assertOk();
        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');

        expect($tickets->count())->toBe(10)
            ->and($tickets->total())->toBe(15)
            ->and($tickets->hasMorePages())->toBeTrue();

        $response->assertSee('search=TCK');
    });
});
