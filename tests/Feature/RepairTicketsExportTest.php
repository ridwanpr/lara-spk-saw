<?php

use App\Actions\RepairTickets\CalculateSawPriorities;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role access for Repair Tickets export endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $this->get(route('repair-tickets.export', ['format' => 'excel']))
            ->assertRedirect(route('login'));
    });

    test('staff can access export and only sees their own tickets', function (): void {
        Excel::fake();

        $staff = User::factory()->staff()->create();
        $otherStaff = User::factory()->staff()->create();
        $asset = Asset::factory()->create();

        $ownTicket = RepairTicket::factory()->create([
            'reporter_id' => $staff->id,
            'asset_id' => $asset->id,
        ]);

        $otherTicket = RepairTicket::factory()->create([
            'reporter_id' => $otherStaff->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('repair-tickets.export', ['format' => 'excel']));

        $response->assertOk();

        Excel::assertDownloaded('laporan_tiket_perbaikan_'.now()->format('Ymd_His').'.xlsx', function ($export) use ($ownTicket, $otherTicket) {
            return $export->collection()->pluck('id')->contains($ownTicket->id)
                && ! $export->collection()->pluck('id')->contains($otherTicket->id);
        });
    });

    test('admin can export tickets across the whole system', function (): void {
        Excel::fake();

        $admin = User::factory()->admin()->create();
        $staff1 = User::factory()->staff()->create();
        $staff2 = User::factory()->staff()->create();
        $asset = Asset::factory()->create();

        $ticket1 = RepairTicket::factory()->create([
            'reporter_id' => $staff1->id,
            'asset_id' => $asset->id,
        ]);

        $ticket2 = RepairTicket::factory()->create([
            'reporter_id' => $staff2->id,
            'asset_id' => $asset->id,
        ]);

        $this->actingAs($admin)
            ->get(route('repair-tickets.export', ['format' => 'excel']))
            ->assertOk();

        Excel::assertDownloaded('laporan_tiket_perbaikan_'.now()->format('Ymd_His').'.xlsx', function ($export) use ($ticket1, $ticket2) {
            return $export->collection()->pluck('id')->contains($ticket1->id)
                && $export->collection()->pluck('id')->contains($ticket2->id);
        });
    });
});

describe('validation rules for export request', function (): void {
    test('validation fails when format is missing or invalid', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson(route('repair-tickets.export'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['format']);

        $this->actingAs($admin)
            ->getJson(route('repair-tickets.export', ['format' => 'csv']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['format']);
    });

    test('validation fails when end_date is before start_date', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson(route('repair-tickets.export', [
                'format' => 'excel',
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-01',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    });

    test('validation fails when status is invalid', function (): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson(route('repair-tickets.export', [
                'format' => 'excel',
                'status' => 'unknown_status',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    });
});

describe('filtering and sorting behavior', function (): void {
    test('filters tickets by status', function (): void {
        Excel::fake();

        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $verifiedTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'status' => TicketStatus::Verified,
        ]);

        $completedTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'status' => TicketStatus::Completed,
        ]);

        $this->actingAs($admin)
            ->get(route('repair-tickets.export', [
                'format' => 'excel',
                'status' => TicketStatus::Verified->value,
            ]))
            ->assertOk();

        Excel::assertDownloaded('laporan_tiket_perbaikan_'.now()->format('Ymd_His').'.xlsx', function ($export) use ($verifiedTicket, $completedTicket) {
            return $export->collection()->pluck('id')->contains($verifiedTicket->id)
                && ! $export->collection()->pluck('id')->contains($completedTicket->id);
        });
    });

    test('filters tickets by date range', function (): void {
        Excel::fake();

        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $inRangeTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'created_at' => '2026-06-15 10:00:00',
        ]);

        $outRangeTicket = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('repair-tickets.export', [
                'format' => 'excel',
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-20',
            ]))
            ->assertOk();

        Excel::assertDownloaded('laporan_tiket_perbaikan_'.now()->format('Ymd_His').'.xlsx', function ($export) use ($inRangeTicket, $outRangeTicket) {
            return $export->collection()->pluck('id')->contains($inRangeTicket->id)
                && ! $export->collection()->pluck('id')->contains($outRangeTicket->id);
        });
    });

    test('sorts tickets by SAW priority when requested', function (): void {
        Excel::fake();

        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $ticketA = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'status' => TicketStatus::Verified,
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $ticketB = RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'status' => TicketStatus::Verified,
            'created_at' => '2026-06-02 10:00:00',
        ]);

        $mockCalculator = Mockery::mock(CalculateSawPriorities::class);
        $mockCalculator->shouldReceive('calculate')->andReturn([
            $ticketA->id => ['score' => 0.45, 'rank' => 2, 'total' => 2],
            $ticketB->id => ['score' => 0.95, 'rank' => 1, 'total' => 2],
        ]);
        $this->app->instance(CalculateSawPriorities::class, $mockCalculator);

        $this->actingAs($admin)
            ->get(route('repair-tickets.export', [
                'format' => 'excel',
                'sort_by' => 'priority',
            ]))
            ->assertOk();

        Excel::assertDownloaded('laporan_tiket_perbaikan_'.now()->format('Ymd_His').'.xlsx', function ($export) use ($ticketA, $ticketB) {
            $ids = $export->collection()->pluck('id')->all();

            return $ids === [$ticketB->id, $ticketA->id];
        });
    });

    test('downloads PDF format successfully', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        RepairTicket::factory()->create([
            'asset_id' => $asset->id,
            'status' => TicketStatus::Verified,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('repair-tickets.export', [
                'format' => 'pdf',
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });
});
