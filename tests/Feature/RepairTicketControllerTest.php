<?php

use App\Enums\AssetCondition;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\Room;
use App\Models\SubCriteria;
use App\Models\TicketEvaluation;
use App\Models\User;
use Database\Seeders\CriteriaSeeder;
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

    test('disables asset options and displays active ticket info for pending, verified, or under repair tickets', function (): void {
        $staff = User::factory()->staff()->create();

        $assetPending = Asset::factory()->create(['name' => 'Laptop Pending', 'code' => 'AST-PND']);
        $ticketPending = RepairTicket::factory()->pendingVerification()->create(['asset_id' => $assetPending->id]);

        $assetVerified = Asset::factory()->create(['name' => 'Proyektor Verified', 'code' => 'AST-VRF']);
        $ticketVerified = RepairTicket::factory()->verified()->create(['asset_id' => $assetVerified->id]);

        $assetRepair = Asset::factory()->create(['name' => 'Printer Repair', 'code' => 'AST-RPR']);
        $ticketRepair = RepairTicket::factory()->underRepair()->create(['asset_id' => $assetRepair->id]);

        $response = $this->actingAs($staff)
            ->get(route('repair-tickets.create'));

        $response->assertOk();

        $response->assertSeeHtml('<option value="'.$assetPending->id.'" disabled>');
        $response->assertSeeText("[Laporan Aktif: #{$ticketPending->ticket_number} - {$ticketPending->status->label()}]");

        $response->assertSeeHtml('<option value="'.$assetVerified->id.'" disabled>');
        $response->assertSeeText("[Laporan Aktif: #{$ticketVerified->ticket_number} - {$ticketVerified->status->label()}]");

        $response->assertSeeHtml('<option value="'.$assetRepair->id.'" disabled>');
        $response->assertSeeText("[Laporan Aktif: #{$ticketRepair->ticket_number} - {$ticketRepair->status->label()}]");
    });

    test('allows selecting assets that only have completed or rejected tickets', function (): void {
        $staff = User::factory()->staff()->create();

        $assetCompleted = Asset::factory()->create(['name' => 'AC Completed', 'code' => 'AST-CMP']);
        RepairTicket::factory()->completed()->create(['asset_id' => $assetCompleted->id]);

        $assetRejected = Asset::factory()->create(['name' => 'Kursi Rejected', 'code' => 'AST-REJ']);
        RepairTicket::factory()->rejected()->create(['asset_id' => $assetRejected->id]);

        $response = $this->actingAs($staff)
            ->get(route('repair-tickets.create'));

        $response->assertOk();

        $response->assertDontSeeHtml('<option value="'.$assetCompleted->id.'" disabled>');
        $response->assertSeeText("{$assetCompleted->name} ({$assetCompleted->code})");

        $response->assertDontSeeHtml('<option value="'.$assetRejected->id.'" disabled>');
        $response->assertSeeText("{$assetRejected->name} ({$assetRejected->code})");

        $response->assertDontSeeText('[Laporan Aktif:');
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

describe('security and role access for Repair Tickets show endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $ticket = RepairTicket::factory()->create();

        $this->get(route('repair-tickets.show', $ticket->ticket_number))
            ->assertRedirect(route('login'));
    });

    test('admin can access repair tickets show endpoint', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->create();

        $this->actingAs($admin)
            ->get(route('repair-tickets.show', $ticket->ticket_number))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.show');
    });

    test('inspector can access repair tickets show endpoint', function (): void {
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->create();

        $this->actingAs($inspector)
            ->get(route('repair-tickets.show', $ticket->ticket_number))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.show');
    });

    test('staff can access repair tickets show endpoint', function (): void {
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->create(['reporter_id' => $staff->id]);

        $this->actingAs($staff)
            ->get(route('repair-tickets.show', $ticket->ticket_number))
            ->assertOk()
            ->assertViewIs('shared.repair-tickets.show');
    });

    test('returns 404 when repair ticket is not found by ticket number', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('repair-tickets.show', 'TCK-NONEXISTENT'))
            ->assertNotFound();
    });
});

describe('repair ticket details display (show)', function (): void {
    test('displays ticket details, asset info, reporter, and damage description', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create([
            'damage_description' => 'Layar LCD proyektor pecah.',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.show', $ticket->ticket_number));

        $response->assertOk();
        $response->assertViewHasAll(['repairTicket', 'criterias', 'ticketPriority']);
        $response->assertSeeText($ticket->ticket_number);
        $response->assertSeeText($ticket->asset->name);
        $response->assertSeeText($ticket->asset->code);
        $response->assertSeeText($ticket->asset->room->name);
        $response->assertSeeText($ticket->asset->category->name);
        $response->assertSeeText('Layar LCD proyektor pecah.');
        $response->assertSeeText($ticket->reporter->name);
    });

    test('shows verification form to admin and inspector when ticket is pending verification', function (): void {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $adminResponse = $this->actingAs($admin)->get(route('repair-tickets.show', $ticket->ticket_number));
        $adminResponse->assertSeeText('Verifikasi & Isi Pemeriksaan');
        $adminResponse->assertSee(route('repair-tickets.verify', $ticket->id));

        $inspectorResponse = $this->actingAs($inspector)->get(route('repair-tickets.show', $ticket->ticket_number));
        $inspectorResponse->assertSeeText('Verifikasi & Isi Pemeriksaan');
        $inspectorResponse->assertSee(route('repair-tickets.verify', $ticket->id));

        $staffResponse = $this->actingAs($staff)->get(route('repair-tickets.show', $ticket->ticket_number));
        $staffResponse->assertDontSeeText('Verifikasi & Isi Pemeriksaan');
        $staffResponse->assertDontSeeText('Tindakan Laporan');
    });

    test('displays verification details, evaluations, and SAW priority badge when ticket is verified', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();
        $verifier = User::factory()->inspector()->create(['name' => 'Bambang Verifikator']);
        $ticket = RepairTicket::factory()->verified()->create([
            'verifier_id' => $verifier->id,
            'estimated_cost' => 350000.00,
        ]);

        $criterias = Criteria::all();
        foreach ($criterias as $criteria) {
            TicketEvaluation::create([
                'ticket_id' => $ticket->id,
                'criteria_id' => $criteria->id,
                'sub_criteria_id' => $criteria->subCriterias->first()->id,
                'score' => $criteria->subCriterias->first()->crisp_value,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.show', $ticket->ticket_number));

        $response->assertOk();
        $response->assertSee('Bambang Verifikator');
        $response->assertSee('350.000');
        $response->assertSee('Rekomendasi Antrean');
        $response->assertSee('#1');
    });
});

describe('security and role access for Repair Tickets verify endpoint', function (): void {
    test('unauthenticated guests cannot verify a ticket and are redirected to login', function (): void {
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $this->post(route('repair-tickets.verify', $ticket->id), [
            'estimated_cost' => 100000,
            'evaluations' => [],
        ])->assertRedirect(route('login'));
    });

    test('staff user is forbidden from verifying a repair ticket', function (): void {
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $this->actingAs($staff)->post(route('repair-tickets.verify', $ticket->id), [
            'estimated_cost' => 100000,
            'evaluations' => [],
        ])->assertForbidden();
    });

    test('cannot verify a ticket that is already verified or in non-pending status', function (): void {
        $this->seed(CriteriaSeeder::class);
        $inspector = User::factory()->inspector()->create();
        $verifiedTicket = RepairTicket::factory()->verified()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();

        $response = $this->actingAs($inspector)
            ->from(route('repair-tickets.show', $verifiedTicket->ticket_number))
            ->post(route('repair-tickets.verify', $verifiedTicket->id), [
                'estimated_cost' => 200000,
                'evaluations' => [
                    $c1->id => $c1->subCriterias->first()->id,
                ],
            ]);

        $response->assertRedirect(route('repair-tickets.show', $verifiedTicket->ticket_number));
        $response->assertSessionHas('error', 'Tiket ini sudah diverifikasi sebelumnya atau statusnya tidak valid.');
    });

    test('returns 404 when verifying a non-existent ticket id', function (): void {
        $this->seed(CriteriaSeeder::class);
        $inspector = User::factory()->inspector()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();

        $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', 999999), [
                'estimated_cost' => 200000,
                'evaluations' => [
                    $c1->id => $c1->subCriterias->first()->id,
                ],
            ])
            ->assertNotFound();
    });
});

describe('validation for Repair Tickets verify endpoint', function (): void {
    test('validates required fields estimated_cost and evaluations', function (): void {
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', $ticket->id), []);

        $response->assertSessionHasErrors(['estimated_cost', 'evaluations']);
    });

    test('validates estimated_cost must be numeric and non-negative', function (mixed $invalidCost): void {
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => $invalidCost,
                'evaluations' => [],
            ]);

        $response->assertSessionHasErrors(['estimated_cost']);
    })->with([
        'negative' => [-50000],
        'non-numeric' => ['bukan-angka'],
    ]);

    test('validates evaluations for manual criteria like C1', function (): void {
        $this->seed(CriteriaSeeder::class);
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();

        // Missing C1 evaluation
        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => 150000,
                'evaluations' => [],
            ]);
        $response->assertSessionHasErrors(["evaluations.{$c1->id}"]);

        // Invalid sub criteria id for C1
        $invalidResponse = $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => 150000,
                'evaluations' => [
                    $c1->id => 999999,
                ],
            ]);
        $invalidResponse->assertSessionHasErrors(["evaluations.{$c1->id}"]);
    });
});

describe('ticket verification and automated criteria evaluation', function (): void {
    test('inspector can verify ticket and system automatically evaluates all criteria', function (): void {
        $this->seed(CriteriaSeeder::class);
        $inspector = User::factory()->inspector()->create();

        $criterias = Criteria::with('subCriterias')->get()->keyBy('code');
        $c1 = $criterias->get('C1');
        $c2 = $criterias->get('C2');
        $c3 = $criterias->get('C3');
        $c4 = $criterias->get('C4');

        // C3 sub criteria: 'Ruang Kelas / Lab Komputer' (crisp 5)
        $labSubCriteria = $c3->subCriterias->firstWhere('crisp_value', 5);
        $room = Room::factory()->create(['sub_criteria_id' => $labSubCriteria->id]);

        // C4 asset procurement year: now()->year - 3 -> 3 years old (crisp 3 for 2-4 years)
        $asset = Asset::factory()->create([
            'room_id' => $room->id,
            'procurement_year' => now()->year - 3,
        ]);

        $ticket = RepairTicket::factory()->pendingVerification()->create([
            'asset_id' => $asset->id,
        ]);

        // C1 chosen: 'Rusak Sedang' (crisp 3)
        $c1SubCriteria = $c1->subCriterias->firstWhere('crisp_value', 3);

        // C2 estimated cost: 150000 (< 250000, crisp 1)
        $estimatedCost = 150000;

        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => $estimatedCost,
                'evaluations' => [
                    $c1->id => $c1SubCriteria->id,
                ],
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success', 'Laporan tiket berhasil diverifikasi dan dievaluasi.');

        $ticket->refresh();
        expect($ticket->status)->toBe(TicketStatus::Verified)
            ->and($ticket->verifier_id)->toBe($inspector->id)
            ->and($ticket->verified_at)->not->toBeNull()
            ->and((float) $ticket->estimated_cost)->toBe(150000.0);

        // Check evaluations created in database
        $c1Eval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $c1->id)->first();
        expect($c1Eval)->not->toBeNull()
            ->and($c1Eval->sub_criteria_id)->toBe($c1SubCriteria->id)
            ->and($c1Eval->score)->toBe(3);

        $c2Eval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $c2->id)->first();
        expect($c2Eval)->not->toBeNull()
            ->and($c2Eval->score)->toBe(1);

        $c3Eval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $c3->id)->first();
        expect($c3Eval)->not->toBeNull()
            ->and($c3Eval->sub_criteria_id)->toBe($labSubCriteria->id)
            ->and($c3Eval->score)->toBe(5);

        $c4Eval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $c4->id)->first();
        expect($c4Eval)->not->toBeNull()
            ->and($c4Eval->score)->toBe(3);
    });

    test('admin can also verify a repair ticket', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();
        $c1Sub = $c1->subCriterias->first();

        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => 500000,
                'evaluations' => [
                    $c1->id => $c1Sub->id,
                ],
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success');

        expect($ticket->fresh()->status)->toBe(TicketStatus::Verified)
            ->and($ticket->fresh()->verifier_id)->toBe($admin->id);
    });

    test('sets C3 score to 0 when asset room has no category sub criteria', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();
        $c3 = Criteria::where('code', 'C3')->firstOrFail();
        $c1Sub = $c1->subCriterias->first();

        $roomWithoutCategory = Room::factory()->create(['sub_criteria_id' => null]);
        $asset = Asset::factory()->create(['room_id' => $roomWithoutCategory->id]);
        $ticket = RepairTicket::factory()->pendingVerification()->create(['asset_id' => $asset->id]);

        $this->actingAs($admin)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => 300000,
                'evaluations' => [$c1->id => $c1Sub->id],
            ]);

        $c3Eval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $c3->id)->first();
        expect($c3Eval)->toBeNull();
    });

    test('evaluates custom non-system range and categorical criteria during verification', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();
        $c1 = Criteria::where('code', 'C1')->firstOrFail();
        $c3 = Criteria::where('code', 'C3')->firstOrFail();
        $room = Room::factory()->create(['sub_criteria_id' => $c3->subCriterias->first()->id]);
        $asset = Asset::factory()->create(['room_id' => $room->id, 'procurement_year' => now()->year]);

        // Custom categorical criteria
        $customCat = Criteria::factory()->categorical()->create(['code' => 'C5', 'is_system' => false]);
        $catSub = SubCriteria::create(['criteria_id' => $customCat->id, 'description' => 'Tinggi', 'crisp_value' => 5]);

        // Custom range criteria
        $customRange = Criteria::factory()->range()->create(['code' => 'C6', 'is_system' => false]);
        $rangeSub = SubCriteria::create(['criteria_id' => $customRange->id, 'description' => '10-20 unit', 'crisp_value' => 4, 'min_value' => 10, 'max_value' => 20]);

        $ticket = RepairTicket::factory()->pendingVerification()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.verify', $ticket->id), [
                'estimated_cost' => 200000,
                'evaluations' => [
                    $c1->id => $c1->subCriterias->first()->id,
                    $customCat->id => $catSub->id,
                    $customRange->id => 15,
                ],
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number));

        $catEval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $customCat->id)->first();
        expect($catEval->sub_criteria_id)->toBe($catSub->id)
            ->and($catEval->score)->toBe(5);

        $rangeEval = TicketEvaluation::where('ticket_id', $ticket->id)->where('criteria_id', $customRange->id)->first();
        expect($rangeEval->sub_criteria_id)->toBe($rangeSub->id)
            ->and($rangeEval->score)->toBe(4);
    });
});

describe('SAW priority calculation and verified tickets ordering', function (): void {
    test('orders verified tickets by SAW rank ascending on index when filtered by status verified', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();

        $criterias = Criteria::all()->keyBy('code');
        $c1 = $criterias->get('C1');
        $c2 = $criterias->get('C2');
        $c3 = $criterias->get('C3');
        $c4 = $criterias->get('C4');

        // Ticket High Priority: high benefit scores, low cost score
        $ticketHigh = RepairTicket::factory()->verified()->create(['ticket_number' => 'TCK-HIGH']);
        TicketEvaluation::create(['ticket_id' => $ticketHigh->id, 'criteria_id' => $c1->id, 'sub_criteria_id' => $c1->subCriterias->firstWhere('crisp_value', 5)->id, 'score' => 5]);
        TicketEvaluation::create(['ticket_id' => $ticketHigh->id, 'criteria_id' => $c2->id, 'sub_criteria_id' => $c2->subCriterias->firstWhere('crisp_value', 1)->id, 'score' => 1]);
        TicketEvaluation::create(['ticket_id' => $ticketHigh->id, 'criteria_id' => $c3->id, 'sub_criteria_id' => $c3->subCriterias->firstWhere('crisp_value', 5)->id, 'score' => 5]);
        TicketEvaluation::create(['ticket_id' => $ticketHigh->id, 'criteria_id' => $c4->id, 'sub_criteria_id' => $c4->subCriterias->firstWhere('crisp_value', 5)->id, 'score' => 5]);

        // Ticket Low Priority: low benefit scores, high cost score
        $ticketLow = RepairTicket::factory()->verified()->create(['ticket_number' => 'TCK-LOW']);
        TicketEvaluation::create(['ticket_id' => $ticketLow->id, 'criteria_id' => $c1->id, 'sub_criteria_id' => $c1->subCriterias->firstWhere('crisp_value', 1)->id, 'score' => 1]);
        TicketEvaluation::create(['ticket_id' => $ticketLow->id, 'criteria_id' => $c2->id, 'sub_criteria_id' => $c2->subCriterias->firstWhere('crisp_value', 5)->id, 'score' => 5]);
        TicketEvaluation::create(['ticket_id' => $ticketLow->id, 'criteria_id' => $c3->id, 'sub_criteria_id' => $c3->subCriterias->firstWhere('crisp_value', 1)->id, 'score' => 1]);
        TicketEvaluation::create(['ticket_id' => $ticketLow->id, 'criteria_id' => $c4->id, 'sub_criteria_id' => $c4->subCriterias->firstWhere('crisp_value', 1)->id, 'score' => 1]);

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.index', ['status' => TicketStatus::Verified->value]));

        $response->assertOk();
        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');
        expect($tickets->first()->id)->toBe($ticketHigh->id)
            ->and($tickets->last()->id)->toBe($ticketLow->id);

        $response->assertSeeText('Urutan #1');
        $response->assertSeeText('Urutan #2');
    });

    test('resolves tie in SAW priority score by prioritizing earlier created ticket', function (): void {
        $this->seed(CriteriaSeeder::class);
        $admin = User::factory()->admin()->create();

        $criterias = Criteria::all()->keyBy('code');
        $c1 = $criterias->get('C1');
        $c2 = $criterias->get('C2');
        $c3 = $criterias->get('C3');
        $c4 = $criterias->get('C4');

        $c1Sub = $c1->subCriterias->first();
        $c2Sub = $c2->subCriterias->first();
        $c3Sub = $c3->subCriterias->first();
        $c4Sub = $c4->subCriterias->first();

        $ticketOld = RepairTicket::factory()->verified()->create([
            'created_at' => now()->subHours(2),
            'ticket_number' => 'TCK-OLD-001',
        ]);
        $ticketNew = RepairTicket::factory()->verified()->create([
            'created_at' => now(),
            'ticket_number' => 'TCK-NEW-002',
        ]);

        foreach ([$ticketOld, $ticketNew] as $ticket) {
            TicketEvaluation::create(['ticket_id' => $ticket->id, 'criteria_id' => $c1->id, 'sub_criteria_id' => $c1Sub->id, 'score' => $c1Sub->crisp_value]);
            TicketEvaluation::create(['ticket_id' => $ticket->id, 'criteria_id' => $c2->id, 'sub_criteria_id' => $c2Sub->id, 'score' => $c2Sub->crisp_value]);
            TicketEvaluation::create(['ticket_id' => $ticket->id, 'criteria_id' => $c3->id, 'sub_criteria_id' => $c3Sub->id, 'score' => $c3Sub->crisp_value]);
            TicketEvaluation::create(['ticket_id' => $ticket->id, 'criteria_id' => $c4->id, 'sub_criteria_id' => $c4Sub->id, 'score' => $c4Sub->crisp_value]);
        }

        $response = $this->actingAs($admin)->get(route('repair-tickets.index', ['status' => TicketStatus::Verified->value]));

        $response->assertOk();
        /** @var LengthAwarePaginator $tickets */
        $tickets = $response->viewData('repairTickets');
        expect($tickets->first()->id)->toBe($ticketOld->id)
            ->and($tickets->last()->id)->toBe($ticketNew->id);

        /** @var array $priorities */
        $priorities = $response->viewData('priorities');
        expect($priorities[$ticketOld->id]['rank'])->toBe(1)
            ->and($priorities[$ticketNew->id]['rank'])->toBe(2)
            ->and($priorities[$ticketOld->id]['score'])->toBe($priorities[$ticketNew->id]['score']);
    });
});

describe('security and role access for Repair Tickets startRepair endpoint', function (): void {
    test('unauthenticated guests cannot start repair and are redirected to login', function (): void {
        $ticket = RepairTicket::factory()->verified()->create();

        $this->post(route('repair-tickets.start-repair', $ticket->id))
            ->assertRedirect(route('login'));
    });

    test('admin can start repair on verified ticket and syncs asset condition to under_repair', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create(['condition' => AssetCondition::Damaged]);
        $ticket = RepairTicket::factory()->verified()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.start-repair', $ticket->id));

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success', 'Proses perbaikan berhasil dimulai.');

        expect($ticket->fresh()->status)->toBe(TicketStatus::UnderRepair)
            ->and($asset->fresh()->condition)->toBe(AssetCondition::UnderRepair);
    });

    test('inspector is forbidden from starting repair', function (): void {
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->verified()->create();

        $this->actingAs($inspector)
            ->post(route('repair-tickets.start-repair', $ticket->id))
            ->assertForbidden();
    });

    test('staff is forbidden from starting repair', function (): void {
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->verified()->create(['reporter_id' => $staff->id]);

        $this->actingAs($staff)
            ->post(route('repair-tickets.start-repair', $ticket->id))
            ->assertForbidden();
    });

    test('cannot start repair on ticket that is not verified', function (): void {
        $admin = User::factory()->admin()->create();
        $pendingTicket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)
            ->from(route('repair-tickets.show', $pendingTicket->ticket_number))
            ->post(route('repair-tickets.start-repair', $pendingTicket->id));

        $response->assertRedirect(route('repair-tickets.show', $pendingTicket->ticket_number))
            ->assertSessionHas('error');

        expect($pendingTicket->fresh()->status)->toBe(TicketStatus::PendingVerification);
    });
});

describe('security and role access for Repair Tickets completeRepair endpoint', function (): void {
    test('unauthenticated guests cannot complete repair and are redirected to login', function (): void {
        $ticket = RepairTicket::factory()->underRepair()->create();

        $this->post(route('repair-tickets.complete-repair', $ticket->id), [
            'actual_cost' => 200000,
        ])->assertRedirect(route('login'));
    });

    test('admin can complete repair and syncs asset condition to good', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create(['condition' => AssetCondition::UnderRepair]);
        $ticket = RepairTicket::factory()->underRepair()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.complete-repair', $ticket->id), [
                'actual_cost' => 320000,
                'completion_notes' => 'Penggantian kabel fleksibel dan pembersihan debu pendingin.',
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success', 'Perbaikan berhasil ditandai selesai.');

        $freshTicket = $ticket->fresh();
        expect($freshTicket->status)->toBe(TicketStatus::Completed)
            ->and((float) $freshTicket->actual_cost)->toBe(320000.0)
            ->and($freshTicket->completion_notes)->toBe('Penggantian kabel fleksibel dan pembersihan debu pendingin.')
            ->and($asset->fresh()->condition)->toBe(AssetCondition::Good);
    });

    test('inspector can complete repair', function (): void {
        $inspector = User::factory()->inspector()->create();
        $asset = Asset::factory()->create(['condition' => AssetCondition::UnderRepair]);
        $ticket = RepairTicket::factory()->underRepair()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.complete-repair', $ticket->id), [
                'actual_cost' => 150000,
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success');

        expect($ticket->fresh()->status)->toBe(TicketStatus::Completed)
            ->and($asset->fresh()->condition)->toBe(AssetCondition::Good);
    });

    test('staff is forbidden from completing repair', function (): void {
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->underRepair()->create(['reporter_id' => $staff->id]);

        $this->actingAs($staff)
            ->post(route('repair-tickets.complete-repair', $ticket->id), [
                'actual_cost' => 100000,
            ])
            ->assertForbidden();
    });

    test('validates actual_cost is required, numeric, and non-negative', function (mixed $invalidCost): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->underRepair()->create();

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.complete-repair', $ticket->id), [
                'actual_cost' => $invalidCost,
            ]);

        $response->assertSessionHasErrors(['actual_cost']);
    })->with([
        'missing' => [null],
        'negative' => [-1000],
        'non-numeric' => ['tidak-valid'],
    ]);

    test('cannot complete repair on ticket that is not under_repair', function (): void {
        $admin = User::factory()->admin()->create();
        $verifiedTicket = RepairTicket::factory()->verified()->create();

        $response = $this->actingAs($admin)
            ->from(route('repair-tickets.show', $verifiedTicket->ticket_number))
            ->post(route('repair-tickets.complete-repair', $verifiedTicket->id), [
                'actual_cost' => 100000,
            ]);

        $response->assertRedirect(route('repair-tickets.show', $verifiedTicket->ticket_number))
            ->assertSessionHas('error');

        expect($verifiedTicket->fresh()->status)->toBe(TicketStatus::Verified);
    });
});

describe('security and role access for Repair Tickets reject endpoint', function (): void {
    test('unauthenticated guests cannot reject a ticket and are redirected to login', function (): void {
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $this->post(route('repair-tickets.reject', $ticket->id), [
            'rejection_reason' => 'Bukan kerusakan fisik.',
        ])->assertRedirect(route('login'));
    });

    test('admin can reject pending ticket with reason', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.reject', $ticket->id), [
                'rejection_reason' => 'Aset hanya butuh restart kabel daya, bukan perbaikan teknis.',
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success', 'Laporan tiket berhasil ditolak.');

        $fresh = $ticket->fresh();
        expect($fresh->status)->toBe(TicketStatus::Rejected)
            ->and($fresh->verifier_id)->toBe($admin->id)
            ->and($fresh->verified_at)->not->toBeNull()
            ->and($fresh->completion_notes)->toBe('Aset hanya butuh restart kabel daya, bukan perbaikan teknis.');
    });

    test('inspector can reject pending ticket', function (): void {
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($inspector)
            ->post(route('repair-tickets.reject', $ticket->id), [
                'rejection_reason' => 'Kerusakan akibat kelalaian operasional tidak ditanggung.',
            ]);

        $response->assertRedirect(route('repair-tickets.show', $ticket->ticket_number))
            ->assertSessionHas('success');

        expect($ticket->fresh()->status)->toBe(TicketStatus::Rejected);
    });

    test('staff is forbidden from rejecting ticket', function (): void {
        $staff = User::factory()->staff()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $this->actingAs($staff)
            ->post(route('repair-tickets.reject', $ticket->id), [
                'rejection_reason' => 'Mencoba menolak tiket sendiri.',
            ])
            ->assertForbidden();
    });

    test('validates rejection_reason is required', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)
            ->post(route('repair-tickets.reject', $ticket->id), [
                'rejection_reason' => '',
            ]);

        $response->assertSessionHasErrors(['rejection_reason']);
    });

    test('cannot reject ticket that is already verified, under_repair, or completed', function (): void {
        $admin = User::factory()->admin()->create();
        $verifiedTicket = RepairTicket::factory()->verified()->create();

        $response = $this->actingAs($admin)
            ->from(route('repair-tickets.show', $verifiedTicket->ticket_number))
            ->post(route('repair-tickets.reject', $verifiedTicket->id), [
                'rejection_reason' => 'Coba tolak yang sudah diverifikasi.',
            ]);

        $response->assertRedirect(route('repair-tickets.show', $verifiedTicket->ticket_number))
            ->assertSessionHas('error');

        expect($verifiedTicket->fresh()->status)->toBe(TicketStatus::Verified);
    });
});

describe('repair ticket action modals rendering on show page', function (): void {
    test('renders reject modal and verify modal for pending ticket to admin and inspector', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)->get(route('repair-tickets.show', $ticket->ticket_number));

        $response->assertOk();
        $response->assertSee('id="verify-ticket-modal"', false);
        $response->assertSee('id="reject-ticket-modal"', false);
        $response->assertSee(route('repair-tickets.reject', $ticket->id));
    });

    test('renders start repair modal for verified ticket only to admin', function (): void {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->verified()->create();

        $adminResponse = $this->actingAs($admin)->get(route('repair-tickets.show', $ticket->ticket_number));
        $adminResponse->assertSee('id="start-repair-modal"', false);
        $adminResponse->assertSee(route('repair-tickets.start-repair', $ticket->id));

        $inspectorResponse = $this->actingAs($inspector)->get(route('repair-tickets.show', $ticket->ticket_number));
        $inspectorResponse->assertDontSee('id="start-repair-modal"', false);
    });

    test('renders complete repair modal for under_repair ticket to admin and inspector', function (): void {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $ticket = RepairTicket::factory()->underRepair()->create();

        $adminResponse = $this->actingAs($admin)->get(route('repair-tickets.show', $ticket->ticket_number));
        $adminResponse->assertSee('id="complete-repair-modal"', false);
        $adminResponse->assertSee(route('repair-tickets.complete-repair', $ticket->id));

        $inspectorResponse = $this->actingAs($inspector)->get(route('repair-tickets.show', $ticket->ticket_number));
        $inspectorResponse->assertSee('id="complete-repair-modal"', false);
    });

    test('renders rejection reason on show page when ticket is rejected', function (): void {
        $admin = User::factory()->admin()->create();
        $ticket = RepairTicket::factory()->rejected()->create([
            'completion_notes' => 'Alasan: Kerusakan di luar klausul pemeliharaan.',
        ]);

        $response = $this->actingAs($admin)->get(route('repair-tickets.show', $ticket->ticket_number));
        $response->assertSee('Alasan Penolakan');
        $response->assertSee('Alasan: Kerusakan di luar klausul pemeliharaan.');
    });
});
