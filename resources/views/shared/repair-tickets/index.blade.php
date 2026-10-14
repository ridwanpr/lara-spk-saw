@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Tiket Perbaikan
                </div>
                <h2 class="page-title">
                    Laporan Aset Rusak
                </h2>
                <div class="text-secondary small mt-1">
                    Review dan proses tiket laporan perbaikan.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('repair-tickets.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"
                        aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Tambah Tiket
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Daftar Tiket</h3>
            <div class="card-actions">
                <form action="{{ route('repair-tickets.index') }}" method="GET"
                    class="d-flex gap-2 align-items-center flex-wrap">
                    {{-- Multi-field Search input --}}
                    <div class="input-icon">
                        <span class="input-icon-addon h-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-sm" aria-hidden="true" focusable="false">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                <path d="M21 21l-6 -6" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ (string) $search }}"
                            class="form-control form-control-sm" placeholder="Cari tiket, aset, pelapor...">
                    </div>

                    {{-- Status Filter --}}
                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ $status === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>

                    @if ($search !== '' || $status !== '')
                        <a href="{{ route('repair-tickets.index') }}" class="btn btn-sm btn-ghost-secondary">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th class="w-1">Kode Tiket</th>
                        <th>Aset</th>
                        <th>Keterangan Kerusakan</th>
                        <th>Pelapor</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repairTickets as $ticket)
                        <tr>
                            <td class="text-nowrap">
                                <span class="font-monospace fw-medium text-body">
                                    {{ $ticket->ticket_number }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-medium text-body">{{ $ticket->asset->name }}</div>
                                <div class="text-secondary small font-monospace">{{ $ticket->asset->code }}</div>
                            </td>

                            <td class="text-secondary" style="max-width: 280px;">
                                <div class="text-truncate" title="{{ $ticket->damage_description }}">
                                    {{ $ticket->damage_description ?: '-' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="text-body">{{ $ticket->reporter->name }}</div>
                                <div class="text-secondary small">
                                    {{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge bg-{{ $ticket->status->color() }}-lt">
                                    <span class="status-dot status-{{ $ticket->status->color() }} me-1"></span>
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                @if (isset($priorities[$ticket->id]))
                                    <div class="fw-medium text-body">
                                        Urutan #{{ $priorities[$ticket->id]['rank'] }}
                                    </div>
                                    <div class="text-secondary small">
                                        Skor: {{ number_format($priorities[$ticket->id]['score'], 2) }}
                                    </div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>

                            <td class="text-end text-nowrap">
                                <a href="{{ route('repair-tickets.show', $ticket->ticket_number) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <div class="mb-1 fw-medium">Data tiket tidak ditemukan</div>
                                <div class="small">
                                    {{ $search !== '' || $status !== '' ? 'Tidak ada tiket yang cocok dengan kriteria filter.' : 'Belum ada laporan perbaikan aset yang masuk.' }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($repairTickets->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-secondary">
                    Menampilkan {{ $repairTickets->firstItem() ?? 0 }} sampai {{ $repairTickets->lastItem() ?? 0 }} dari
                    {{ $repairTickets->total() }} tiket
                </div>
                <div>
                    {{ $repairTickets->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
