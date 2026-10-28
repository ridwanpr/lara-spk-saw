@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Laporan Aset Rusak
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#modal-export-tickets">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"
                        aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                        <path d="M7 11l5 5l5 -5" />
                        <path d="M12 4l0 12" />
                    </svg>
                    Export
                </button>
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
                                    @php
                                        $pRank = $priorities[$ticket->id]['rank'];
                                        $pScore = $priorities[$ticket->id]['score'];
                                    @endphp
                                    <span class="badge {{ $pRank === 1 ? 'badge-priority-top text-white' : 'badge-priority' }} mb-1">
                                        Urutan #{{ $pRank }}
                                    </span>
                                    <div class="text-secondary small font-monospace">
                                        Skor: {{ number_format($pScore, 4) }}
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

    {{-- Modal Export Tickets --}}
    <div class="modal modal-blur fade" id="modal-export-tickets" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('repair-tickets.export') }}" method="GET">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Laporan Tiket Perbaikan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Format File</label>
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-row">
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="format" value="excel" class="form-selectgroup-input" checked>
                                    <span class="form-selectgroup-label d-flex align-items-center p-3">
                                        <span class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </span>
                                        <span class="form-selectgroup-label-content">
                                            <span class="font-weight-medium">Excel</span>
                                        </span>
                                    </span>
                                </label>
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="format" value="pdf" class="form-selectgroup-input">
                                    <span class="form-selectgroup-label d-flex align-items-center p-3">
                                        <span class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </span>
                                        <span class="form-selectgroup-label-content">
                                            <span class="font-weight-medium">PDF</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="export-status">Status Tiket</label>
                            <select name="status" id="export-status" class="form-select">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $item)
                                    <option value="{{ $item->value }}" @selected($status === $item->value)>
                                        {{ $item->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label" for="export-start-date">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="export-start-date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="export-end-date">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="export-end-date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan Data</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="sort_by" id="sort-latest" value="latest" checked>
                                <label class="form-check-label" for="sort-latest">
                                    Tanggal Terbaru (Waktu pelaporan terkini)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sort_by" id="sort-priority" value="priority">
                                <label class="form-check-label" for="sort-priority">
                                    Prioritas SAW (Skor tertinggi / Ranking teratas)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary ms-auto d-inline-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"
                                aria-hidden="true" focusable="false">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 11l5 5l5 -5" />
                                <path d="M12 4l0 12" />
                            </svg>
                            Unduh Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
