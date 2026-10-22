@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('repair-tickets.index') }}">Laporan Aset Rusak</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="#">Detail Tiket</a>
                    </li>
                </ol>
                <div class="page-pretitle text-secondary">
                    Detail Tiket
                </div>
                <h2 class="page-title font-monospace">
                    {{ $repairTicket->ticket_number }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('repair-tickets.index') }}" class="btn btn-outline-secondary">
                    Kembali
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

    <div class="row row-cards">
        {{-- Kolom Kiri: Informasi Aset & Keluhan Kerusakan --}}
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Informasi Aset & Kerusakan</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid mb-4">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Aset</div>
                            <div class="datagrid-content fw-medium text-body">
                                {{ $repairTicket->asset->name }}
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kode Aset</div>
                            <div class="datagrid-content font-monospace text-body">
                                {{ $repairTicket->asset->code }}
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Lokasi / Ruangan</div>
                            <div class="datagrid-content text-body">
                                {{ $repairTicket->asset->room->name ?? '-' }}
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kategori</div>
                            <div class="datagrid-content text-body">
                                {{ $repairTicket->asset->category->name ?? '-' }}
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tahun Pengadaan</div>
                            <div class="datagrid-content text-body">
                                {{ $repairTicket->asset->procurement_year }}
                            </div>
                        </div>
                    </div>

                    <div class="hr-text my-4">Detail Keluhan</div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Keterangan Kerusakan</label>
                        <div class="p-3 rounded border text-body">
                            {!! nl2br(e($repairTicket->damage_description)) !!}
                        </div>
                    </div>

                    @if ($repairTicket->damage_photo)
                        <div class="mt-3">
                            <label class="form-label text-secondary small">Foto Dokumentasi</label>
                            <div class="border rounded p-2 d-inline-block">
                                <a href="{{ asset('storage/' . $repairTicket->damage_photo) }}" target="_blank"
                                    rel="noopener">
                                    <img src="{{ asset('storage/' . $repairTicket->damage_photo) }}"
                                        alt="Foto kerusakan {{ $repairTicket->asset->name }}" class="img-fluid rounded"
                                        style="max-height: 320px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ditampilkan jika tiket sudah diperiksa teknisi --}}
            @if ($repairTicket->verified_at)
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Hasil Pemeriksaan Teknis</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Diperiksa Oleh</div>
                                <div class="datagrid-content text-body">
                                    {{ $repairTicket->verifier->name ?? '-' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Waktu Pemeriksaan</div>
                                <div class="datagrid-content text-body">
                                    {{ $repairTicket->verified_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Estimasi Biaya Perbaikan</div>
                                <div class="datagrid-content fw-medium text-body">
                                    {{ $repairTicket->estimated_cost !== null ? 'Rp ' . number_format((float) $repairTicket->estimated_cost, 0, ',', '.') : '-' }}
                                </div>
                            </div>
                            @if ($repairTicket->actual_cost !== null)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Biaya Realisasi</div>
                                    <div class="datagrid-content fw-medium text-body">
                                        Rp {{ number_format((float) $repairTicket->actual_cost, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($repairTicket->completion_notes)
                            <div class="mt-3">
                                <label class="form-label text-secondary small">
                                    {{ $repairTicket->status === \App\Enums\TicketStatus::Rejected ? 'Alasan Penolakan' : 'Catatan Perbaikan' }}
                                </label>
                                <div class="p-3 rounded border text-body">
                                    {!! nl2br(e($repairTicket->completion_notes)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Status & Metadata Tiket serta Tindakan --}}
        <div class="col-12 col-lg-4">
            {{-- Metadata Tiket --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Informasi Tiket</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Status</span>
                        <span class="badge bg-{{ $repairTicket->status->color() }}-lt">
                            <span class="status-dot status-{{ $repairTicket->status->color() }} me-1"></span>
                            {{ $repairTicket->status->label() }}
                        </span>
                    </div>
                    @if ($ticketPriority)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary">Rekomendasi Antrean</span>
                            <span class="text-body fw-medium">
                                #{{ $ticketPriority['rank'] }} dari {{ $ticketPriority['total'] }} tiket
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary">Skor Evaluasi</span>
                            <span class="text-body font-monospace">
                                {{ number_format($ticketPriority['score'], 2) }}
                            </span>
                        </div>
                    @endif
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary">Pelapor</span>
                        <span class="text-body fw-medium">{{ $repairTicket->reporter->name }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary">Waktu Lapor</span>
                        <span class="text-body">{{ $repairTicket->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                    @if ($repairTicket->verified_at)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary">Terverifikasi</span>
                            <span class="text-body">{{ $repairTicket->verified_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Panel Tindakan --}}
            @if (!auth()->user()->isStaff())
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Tindakan Laporan</h3>
                    </div>
                    <div class="card-body">
                        @if ($repairTicket->status === \App\Enums\TicketStatus::PendingVerification)
                            <p class="text-secondary small mb-3">
                                Lakukan pengecekan kondisi fisik aset di lokasi sebelum memverifikasi atau menolak laporan
                                ini.
                            </p>
                            <div class="d-flex flex-column gap-3">
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                    data-bs-target="#verify-ticket-modal">
                                    Verifikasi & Isi Pemeriksaan
                                </button>
                                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#reject-ticket-modal">
                                    Tolak Laporan
                                </button>
                            </div>
                        @elseif ($repairTicket->status === \App\Enums\TicketStatus::Verified)
                            <p class="text-secondary small mb-3">
                                Laporan telah diverifikasi dan masuk dalam daftar antrean perbaikan.
                            </p>
                            @if (auth()->user()->isAdmin())
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                    data-bs-target="#start-repair-modal">
                                    Mulai Proses Perbaikan
                                </button>
                            @endif
                        @elseif ($repairTicket->status === \App\Enums\TicketStatus::UnderRepair)
                            <p class="text-secondary small mb-3">
                                Aset sedang dalam proses pengerjaan teknisi. Tandai selesai apabila perbaikan fisik telah
                                tuntas.
                            </p>
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                                data-bs-target="#complete-repair-modal">
                                Selesaikan Perbaikan
                            </button>
                        @else
                            <div class="text-secondary small">
                                Penanganan tiket ini telah selesai dan tidak memerlukan tindakan lebih lanjut.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!auth()->user()->isStaff())
        @if ($repairTicket->status === \App\Enums\TicketStatus::PendingVerification)
            @include('shared.repair-tickets.evaluations.verify-ticket')
            @include('shared.repair-tickets.modals.reject')
        @elseif ($repairTicket->status === \App\Enums\TicketStatus::Verified && auth()->user()->isAdmin())
            @include('shared.repair-tickets.modals.start-repair')
        @elseif ($repairTicket->status === \App\Enums\TicketStatus::UnderRepair)
            @include('shared.repair-tickets.modals.complete-repair')
        @endif
    @endif
@endsection
