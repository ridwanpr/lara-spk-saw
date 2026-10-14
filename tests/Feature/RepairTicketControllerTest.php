<?php

use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role access for Repair Tickets index endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $this->get(route('repair-tickets.index'))
            ->assertRedirect(route('login'));
    });

    test('admin can access repair tickets index endpoint', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('repair-tickets.index'))
            ->assertOk();
    });

    test('inspector can access repair tickets index endpoint', function (): void {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)
            ->get(route('repair-tickets.index'))
            ->assertOk();
    });

    test('staff can access repair tickets index endpoint', function (): void {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('repair-tickets.index'))
            ->assertOk();
    });
});

describe('security and role access for Repair Tickets create endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $this->get(route('repair-tickets.create'))
            ->assertRedirect(route('login'));
    });

    test('admin can access repair tickets create endpoint', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('repair-tickets.create'))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.create');
    });

    test('inspector can access repair tickets create endpoint', function (): void {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)
            ->get(route('repair-tickets.create'))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.create');
    });

    test('staff can access repair tickets create endpoint', function (): void {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('repair-tickets.create'))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.create');
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
            ->get(route('repair-tickets.index'));

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
            ->get(route('repair-tickets.index'));

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
            ->get(route('repair-tickets.index'));

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
            ->get(route('repair-tickets.index'));

        $response->assertOk();
        $response->assertViewIs('shared.repair-tickets.index');
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
            ->get(route('repair-tickets.index'));

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
            ->get(route('repair-tickets.index', ['search' => '9999']));

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
            ->get(route('repair-tickets.index', ['search' => 'Projector']));

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
            ->get(route('repair-tickets.index', ['search' => 'PROJ-01']));

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
            ->get(route('repair-tickets.index', ['search' => 'Budi']));

        $response->assertOk();
        $response->assertSeeText($ticketA->ticket_number);
        $response->assertSeeText('Budi Santoso');
        $response->assertDontSeeText($ticketB->ticket_number);
    });

    test('displays empty search message when search matches no ticket', function (): void {
        $admin = User::factory()->admin()->create();
        RepairTicket::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.index', ['search' => 'nonexistentquery12345']));

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
            ->get(route('repair-tickets.index', ['search' => 'Laptop']));

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
            ->get(route('repair-tickets.index', ['status' => TicketStatus::PendingVerification->value]));

        $response->assertOk();
        $response->assertSeeText($pendingTicket->ticket_number);
        $response->assertDontSeeText($completedTicket->ticket_number);
    });

    test('gracefully ignores invalid status value and lists all tickets', function (): void {
        $admin = User::factory()->admin()->create();
        $ticketA = RepairTicket::factory()->pendingVerification()->create();
        $ticketB = RepairTicket::factory()->completed()->create();

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.index', ['status' => 'invalid_status_value']));

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
            ->get(route('repair-tickets.index', [
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
            ->get(route('repair-tickets.index'));

        $response->assertOk();
        $tickets = $response->viewData('repairTickets');
        expect($tickets->first()->id)->toBe($newTicket->id)
            ->and($tickets->last()->id)->toBe($oldTicket->id);
    });

    test('paginates repair tickets 10 items per page and preserves query strings', function (): void {
        $admin = User::factory()->admin()->create();
        RepairTicket::factory()->count(15)->create();

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.index', ['search' => 'TCK']));

        $response->assertOk();
        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');

        expect($tickets->count())->toBe(10)
            ->and($tickets->total())->toBe(15)
            ->and($tickets->hasMorePages())->toBeTrue();

        $response->assertSee('search=TCK');
    });
});

describe('security and role access for Repair Tickets store endpoint', function (): void {
    test('unauthenticated guests cannot store a repair ticket and are redirected to login', function (): void {
        $asset = Asset::factory()->create();

        $this->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kerusakan pada unit.',
        ])->assertRedirect(route('login'));

        expect(RepairTicket::count())->toBe(0);
    });

    test('staff user can create a repair ticket and is set as reporter', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Layar proyektor tidak menyala.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success', 'Tiket perbaikan berhasil dibuat');

        $this->assertDatabaseHas('repair_tickets', [
            'asset_id' => $asset->id,
            'reporter_id' => $staff->id,
            'damage_description' => 'Layar proyektor tidak menyala.',
            'status' => TicketStatus::PendingVerification->value,
            'damage_photo' => null,
        ]);
    });

    test('admin user can create a repair ticket', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($admin)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kabel power monitor terkelupas.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success', 'Tiket perbaikan berhasil dibuat');

        $this->assertDatabaseHas('repair_tickets', [
            'asset_id' => $asset->id,
            'reporter_id' => $admin->id,
            'damage_description' => 'Kabel power monitor terkelupas.',
            'status' => TicketStatus::PendingVerification->value,
        ]);
    });

    test('inspector user can create a repair ticket', function (): void {
        $inspector = User::factory()->inspector()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($inspector)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kaki meja patah di lab komputer.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success', 'Tiket perbaikan berhasil dibuat');

        $this->assertDatabaseHas('repair_tickets', [
            'asset_id' => $asset->id,
            'reporter_id' => $inspector->id,
            'damage_description' => 'Kaki meja patah di lab komputer.',
            'status' => TicketStatus::PendingVerification->value,
        ]);
    });
});

describe('validation for Repair Tickets store endpoint', function (): void {
    test('validates required fields asset_id and damage_description', function (): void {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), []);

        $response->assertSessionHasErrors(['asset_id', 'damage_description']);
    });

    test('validates asset_id must exist in assets table', function (): void {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => 999999,
            'damage_description' => 'Deskripsi kerusakan valid.',
        ]);

        $response->assertSessionHasErrors(['asset_id']);
    });

    test('validates damage_description min length of 3 characters', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'ab',
        ]);

        $response->assertSessionHasErrors(['damage_description']);
    });

    test('validates damage_photo must be a valid image file type', function (): void {
        Storage::fake('public');
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kerusakan fisik terlihat.',
            'damage_photo' => $pdfFile,
        ]);

        $response->assertSessionHasErrors(['damage_photo']);
    });

    test('validates damage_photo must not exceed 5MB', function (): void {
        Storage::fake('public');
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        $hugeImage = UploadedFile::fake()->create('huge-image.jpg', 6 * 1024, 'image/jpeg');

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kerusakan fisik terlihat.',
            'damage_photo' => $hugeImage,
        ]);

        $response->assertSessionHasErrors(['damage_photo']);
    });
});

describe('damage photo upload handling in Repair Tickets store endpoint', function (): void {
    test('stores damage photo on public disk and saves path to database', function (): void {
        Storage::fake('public');
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        $image = UploadedFile::fake()->image('damage.jpg', 640, 480);

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Layar retak parah.',
            'damage_photo' => $image,
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success', 'Tiket perbaikan berhasil dibuat');

        $ticket = RepairTicket::latest('id')->first();
        expect($ticket)->not->toBeNull()
            ->and($ticket->damage_photo)->not->toBeNull();

        Storage::disk('public')->assertExists($ticket->damage_photo);
    });

    test('allows creating ticket without damage photo', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kerusakan software tanpa bukti foto.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'));

        $ticket = RepairTicket::latest('id')->first();
        expect($ticket->damage_photo)->toBeNull();
    });
});

describe('active ticket conflict prevention in Repair Tickets store endpoint', function (): void {
    test('prevents creating ticket if asset has active ticket pending verification', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        $activeTicket = RepairTicket::factory()->pendingVerification()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($staff)
            ->from(route('repair-tickets.create'))
            ->post(route('repair-tickets.store'), [
                'asset_id' => $asset->id,
                'damage_description' => 'Mencoba membuat tiket kedua.',
            ]);

        $response->assertRedirect(route('repair-tickets.create'));
        $response->assertSessionHas('error');

        expect(RepairTicket::where('asset_id', $asset->id)->count())->toBe(1);
    });

    test('prevents creating ticket if asset has active ticket verified', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        RepairTicket::factory()->verified()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($staff)
            ->from(route('repair-tickets.create'))
            ->post(route('repair-tickets.store'), [
                'asset_id' => $asset->id,
                'damage_description' => 'Mencoba membuat tiket kedua saat terverifikasi.',
            ]);

        $response->assertRedirect(route('repair-tickets.create'));
        $response->assertSessionHas('error');

        expect(RepairTicket::where('asset_id', $asset->id)->count())->toBe(1);
    });

    test('prevents creating ticket if asset has active ticket under repair', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        RepairTicket::factory()->underRepair()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($staff)
            ->from(route('repair-tickets.create'))
            ->post(route('repair-tickets.store'), [
                'asset_id' => $asset->id,
                'damage_description' => 'Mencoba membuat tiket kedua saat sedang diperbaiki.',
            ]);

        $response->assertRedirect(route('repair-tickets.create'));
        $response->assertSessionHas('error');

        expect(RepairTicket::where('asset_id', $asset->id)->count())->toBe(1);
    });

    test('allows creating ticket if previous ticket for asset was completed', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        RepairTicket::factory()->completed()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Kerusakan baru setelah tiket sebelumnya selesai.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success');

        expect(RepairTicket::where('asset_id', $asset->id)->count())->toBe(2);
    });

    test('allows creating ticket if previous ticket for asset was rejected', function (): void {
        $staff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();
        RepairTicket::factory()->rejected()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $asset->id,
            'damage_description' => 'Pengajuan ulang setelah tiket sebelumnya ditolak.',
        ]);

        $response->assertRedirect(route('repair-tickets.index'))
            ->assertSessionHas('success');

        expect(RepairTicket::where('asset_id', $asset->id)->count())->toBe(2);
    });
});

describe('ticket number generation in Repair Tickets store endpoint', function (): void {
    test('generates sequential ticket number with format TKT-YYYYMM-XXXX', function (): void {
        $staff = User::factory()->staff()->create();
        $assetA = Asset::factory()->create();
        $assetB = Asset::factory()->create();
        $prefix = 'TKT-'.now()->format('Ym').'-';

        $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $assetA->id,
            'damage_description' => 'Kerusakan aset pertama.',
        ]);

        $ticketA = RepairTicket::where('asset_id', $assetA->id)->first();
        expect($ticketA->ticket_number)->toBe($prefix.'0001');

        $this->actingAs($staff)->post(route('repair-tickets.store'), [
            'asset_id' => $assetB->id,
            'damage_description' => 'Kerusakan aset kedua.',
        ]);

        $ticketB = RepairTicket::where('asset_id', $assetB->id)->first();
        expect($ticketB->ticket_number)->toBe($prefix.'0002');
    });
});
