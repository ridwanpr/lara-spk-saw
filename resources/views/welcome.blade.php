@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Sistem Pendukung Keputusan
                </div>
                <h2 class="page-title">
                    Dashboard Prioritas Perbaikan
                </h2>
                <div class="text-secondary small mt-1">
                    Selamat datang kembali, <span class="fw-medium text-body">{{ auth()->user()->name }}</span>. Pantau antrean perbaikan aset berdasarkan metode SAW.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <a href="{{ route('repair-tickets.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                        <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2"/>
                        <path d="M9 14l2 2l4 -4"/>
                    </svg>
                    Semua Tiket
                </a>
                <a href="{{ route('repair-tickets.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 5l0 14"/>
                        <path d="M5 12l14 0"/>
                    </svg>
                    Buat Laporan
                </a>
            </div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-primary-lt text-primary rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                    <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2"/>
                                    <path d="M9 12l.01 0"/>
                                    <path d="M13 12l2 0"/>
                                    <path d="M9 16l.01 0"/>
                                    <path d="M13 16l2 0"/>
                                </svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-body fw-bold fs-2">
                                {{ $stats['total'] }}
                            </div>
                            <div class="text-secondary small">
                                Total Laporan Tiket
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-secondary-lt text-secondary rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                    <path d="M12 8l0 4l2 2"/>
                                </svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-body fw-bold fs-2">
                                {{ $stats['pending'] }}
                            </div>
                            <div class="text-secondary small">
                                Menunggu Verifikasi
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-info-lt text-info rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                    <path d="M12 12l8 -4.5"/>
                                    <path d="M12 12l0 9"/>
                                    <path d="M12 12l-8 -4.5"/>
                                </svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-body fw-bold fs-2">
                                {{ $stats['total_assets'] }}
                            </div>
                            <div class="text-secondary small">
                                Jumlah Aset Terdaftar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-success-lt text-success rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-body fw-bold fs-2">
                                {{ $stats['completed'] }}
                            </div>
                            <div class="text-secondary small">
                                Selesai Diperbaiki
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Sections: Priority Queue + SPK Context --}}
    <div class="row row-cards">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">Antrean Prioritas Tertinggi (Metode SAW)</h3>
                        <div class="card-subtitle text-secondary small">
                            Tiket berstatus terverifikasi yang telah melalui kalkulasi normalisasi dan pembobotan.
                        </div>
                    </div>
                    @if ($topPriorities->isNotEmpty())
                        <a href="{{ route('repair-tickets.index', ['status' => 'verified']) }}" class="btn btn-sm btn-ghost-primary">
                            Lihat Semua Antrean
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead>
                            <tr>
                                <th class="w-1">Prioritas</th>
                                <th>No. Tiket</th>
                                <th>Aset & Ruangan</th>
                                <th>Nilai Preferensi (V)</th>
                                <th class="w-1 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topPriorities as $ticket)
                                @php
                                    $rank = $priorities[$ticket->id]['rank'] ?? '-';
                                    $score = $priorities[$ticket->id]['score'] ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        @if ($rank === 1)
                                            <span class="badge badge-priority-top text-white">
                                                #1 Utama
                                            </span>
                                        @else
                                            <span class="badge badge-priority">
                                                #{{ $rank }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-monospace fw-medium text-body">
                                            {{ $ticket->ticket_number }}
                                        </div>
                                        <div class="text-secondary small">
                                            {{ $ticket->created_at?->format('d M Y') ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-body">
                                            {{ $ticket->asset->name }}
                                        </div>
                                        <div class="text-secondary small">
                                            {{ $ticket->asset->room?->name ?? 'Tanpa Ruangan' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($score !== null)
                                            <span class="font-monospace fw-medium text-body">
                                                {{ number_format((float) $score, 4) }}
                                            </span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('repair-tickets.show', $ticket->ticket_number) }}" class="btn btn-sm btn-outline-secondary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-secondary">
                                        <div class="mb-1 fw-medium">Tidak ada tiket dalam antrean prioritas</div>
                                        <div class="small">
                                            Saat ini belum ada tiket berstatus Terverifikasi yang menunggu perbaikan.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Quick Links & Guide --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Alur Penanganan Tiket</h3>
                </div>
                <div class="card-body">
                    <ol class="steps steps-vertical">
                        <li class="step-item">
                            <div class="text-body fw-medium">1. Laporan Kerusakan</div>
                            <div class="text-secondary small">Staff menginput aset & keluhan fisik.</div>
                        </li>
                        <li class="step-item">
                            <div class="text-body fw-medium">2. Verifikasi Inspector</div>
                            <div class="text-secondary small">Pemeriksaan fisik dan input taksiran biaya.</div>
                        </li>
                        <li class="step-item">
                            <div class="text-body fw-medium">3. Perankingan SAW</div>
                            <div class="text-secondary small">Sistem mengurutkan prioritas berdasarkan 4 kriteria.</div>
                        </li>
                        <li class="step-item">
                            <div class="text-body fw-medium">4. Eksekusi Perbaikan</div>
                            <div class="text-secondary small">Teknisi memperbaiki aset berprioritas tinggi.</div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
