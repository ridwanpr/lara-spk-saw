@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Panduan &amp; Spesifikasi Sistem
                </div>
                <h2 class="page-title">
                    Dokumentasi Aplikasi
                </h2>
                <div class="text-secondary small mt-1">
                    Panduan operasional sistem pendukung keputusan penentuan prioritas perbaikan aset dengan metode Simple Additive Weighting.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('repair-tickets.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                        <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2"/>
                        <path d="M9 14l2 2l4 -4"/>
                    </svg>
                    Tiket Perbaikan
                </a>
            </div>
        </div>
    </div>

    <style>
        .doc-section {
            scroll-margin-top: 5.5rem;
        }
        .doc-nav-link {
            display: block;
            padding: 0.45rem 0.75rem;
            border-radius: 6px;
            color: var(--tblr-body-color);
            text-decoration: none;
            font-size: 0.875rem;
            border-left: 3px solid transparent;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .doc-nav-link:hover {
            background-color: color-mix(in srgb, var(--tblr-primary) 8%, transparent);
            color: var(--tblr-primary);
            border-left-color: var(--tblr-primary);
        }
        .doc-formula-box {
            background-color: color-mix(in srgb, var(--tblr-body-bg) 70%, var(--tblr-bg-surface));
            border: 1px solid var(--tblr-border-color);
            border-radius: 6px;
            padding: 1rem;
            font-family: var(--tblr-font-monospace);
            font-size: 0.875rem;
            overflow-x: auto;
        }
    </style>

    <div class="row g-4">
        {{-- Sticky Navigation Sidebar --}}
        <div class="col-12 col-lg-3">
            <div class="card shadow-sm sticky-top" style="top: 1.5rem; z-index: 10;">
                <div class="card-header py-3">
                    <h3 class="card-title fs-4">Daftar Isi</h3>
                </div>
                <div class="card-body p-2">
                    <nav class="nav flex-column gap-1" aria-label="Navigasi Dokumentasi">
                        <a href="#tujuan-sistem" class="doc-nav-link">
                            1. Ikhtisar Sistem
                        </a>
                        <a href="#peran-pengguna" class="doc-nav-link">
                            2. Hak Akses &amp; Peran
                        </a>
                        <a href="#siklus-tiket" class="doc-nav-link">
                            3. Siklus Hidup Tiket
                        </a>
                        <a href="#metode-saw" class="doc-nav-link">
                            4. Metode SAW
                        </a>
                        <a href="#kriteria-bobot" class="doc-nav-link">
                            5. Kriteria &amp; Subkriteria
                        </a>
                        <a href="#laporan-ekspor" class="doc-nav-link">
                            6. Pelaporan &amp; Ekspor
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- Main Documentation Content --}}
        <div class="col-12 col-lg-9">
            <article class="d-flex flex-column gap-4">

                {{-- 1. Ikhtisar Sistem --}}
                <section id="tujuan-sistem" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-primary-lt text-primary rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12l0 9" />
                                    <path d="M12 12l-8 -4.5" />
                                </svg>
                            </span>
                            <h3 class="card-title m-0">1. Ikhtisar Sistem</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-body leading-relaxed mb-3">
                            SIAset SPK SAW mencatat pelaporan kerusakan sarana prasarana sekolah dan menyusun antrean perbaikan aset berdasarkan metode Simple Additive Weighting (SAW). Sistem mengurutkan perbaikan aset secara terukur berdasarkan kriteria kuantitatif dan kualitatif yang telah dikonfigurasi.
                        </p>
                        <p class="text-body leading-relaxed mb-3">
                            Setiap tiket kerusakan yang diverifikasi oleh inspektur memperoleh skor evaluasi pada masing-masing kriteria. Sistem menghitung nilai preferensi relatif setiap tiket terhadap tiket lain yang sedang menunggu perbaikan.
                        </p>

                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded bg-surface-secondary">
                                    <div class="fw-bold text-body mb-1">Manajemen Inventaris</div>
                                    <div class="text-secondary small">
                                        Pencatatan unit aset mencakup kode inventaris, kategori, ruangan penempatan, tahun pengadaan, dan status kelayakan fisik.
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded bg-surface-secondary">
                                    <div class="fw-bold text-body mb-1">Pengambilan Keputusan Obyektif</div>
                                    <div class="text-secondary small">
                                        Urutan penanganan perbaikan dihasilkan otomatis dari matriks evaluasi berbobot tanpa intervensi subyektif.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 2. Hak Akses & Peran Pengguna --}}
                <section id="peran-pengguna" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-info-lt text-info rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                </svg>
                            </span>
                            <h3 class="card-title m-0">2. Hak Akses &amp; Peran Pengguna</h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover card-table m-0">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Peran</th>
                                        <th style="width: 32%;">Tanggung Jawab Utama</th>
                                        <th>Cakupan Izin Sistem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="badge bg-purple-lt fw-bold">Admin</span>
                                        </td>
                                        <td>
                                            Pengelolaan penuh data master dan parameter kalkulasi SPK.
                                        </td>
                                        <td class="small text-secondary">
                                            Mengelola pengguna, ruangan, kategori aset, unit aset, kriteria, subkriteria, serta melihat dan mengekspor seluruh tiket perbaikan.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="badge bg-blue-lt fw-bold">Inspector</span>
                                        </td>
                                        <td>
                                            Pemeriksaan teknis dan penilaian kriteria kerusakan aset.
                                        </td>
                                        <td class="small text-secondary">
                                            Melakukan verifikasi fisik kerusakan, mengisi skor kriteria evaluasi, menginput perkiraan biaya, menolak laporan tidak valid, memulai perbaikan, dan menyelesaikan tiket.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="badge bg-green-lt fw-bold">Staff</span>
                                        </td>
                                        <td>
                                            Pelaporan aset rusak dan pemantauan perbaikan.
                                        </td>
                                        <td class="small text-secondary">
                                            Membuat tiket laporan baru dan memantau status tiket yang dibuat oleh akun bersangkutan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                {{-- 3. Siklus Hidup Tiket Perbaikan --}}
                <section id="siklus-tiket" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-warning-lt text-warning rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                    <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2"/>
                                    <path d="M9 12l.01 0"/>
                                    <path d="M13 12l2 0"/>
                                    <path d="M9 16l.01 0"/>
                                    <path d="M13 16l2 0"/>
                                </svg>
                            </span>
                            <h3 class="card-title m-0">3. Siklus Hidup Tiket Perbaikan</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-secondary-lt font-monospace">1. Menunggu Verifikasi</span>
                                    </div>
                                    <div class="text-secondary small">
                                        Tiket baru masuk dari pelapor. Inspektur meninjau lokasi fisik dan memeriksa kesesuaian kerusakan yang dilaporkan.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-info-lt font-monospace">2. Terverifikasi</span>
                                    </div>
                                    <div class="text-secondary small">
                                        Inspektur memasukkan nilai kriteria dan taksiran biaya servis. Tiket masuk ke dalam antrean perhitungan prioritas SAW.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-warning-lt font-monospace">3. Sedang Diperbaiki</span>
                                    </div>
                                    <div class="text-secondary small">
                                        Teknisi memulai perbaikan aset fisik. Tiket keluar dari antrean kalkulasi SAW karena perbaikan sedang berjalan.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-success-lt font-monospace">4. Selesai</span>
                                    </div>
                                    <div class="text-secondary small">
                                        Pekerjaan fisik selesai, fungsi aset teruji, dan waktu penyelesaian dicatat ke dalam riwayat tiket.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-danger-lt font-monospace">5. Ditolak</span>
                                    </div>
                                    <div class="text-secondary small">
                                        Laporan dinyatakan tidak memenuhi syarat pemeriksaan fisik dengan catatan penolakan dari inspektur.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 4. Metode Simple Additive Weighting (SAW) --}}
                <section id="metode-saw" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-teal-lt text-teal rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 12h4l3 8l4 -16l3 8h4"/>
                                </svg>
                            </span>
                            <h3 class="card-title m-0">4. Metode Simple Additive Weighting (SAW)</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-body leading-relaxed mb-3">
                            Metode SAW menentukan penjumlahan terbobot dari rating kinerja pada setiap alternatif di semua kriteria. Kalkulasi dijalankan pada seluruh tiket berstatus Terverifikasi (<span class="font-monospace">Verified</span>).
                        </p>

                        <div class="mb-4">
                            <h4 class="text-body fw-bold mb-2">Langkah 1: Matriks Keputusan ($X$)</h4>
                            <p class="text-secondary small mb-2">
                                Setiap tiket perbaikan berstatus terverifikasi diperlakukan sebagai alternatif ($A_i$). Nilai $x_{ij}$ merupakan nilai evaluasi kriteria ke-$j$ pada tiket ke-$i$.
                            </p>
                            <div class="doc-formula-box">
                                X = [ x_ij ] untuk i = 1, 2, ..., m (tiket) dan j = 1, 2, ..., n (kriteria)
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-body fw-bold mb-2">Langkah 2: Normalisasi Matriks ($R$)</h4>
                            <p class="text-secondary small mb-2">
                                Elemen matriks dinormalisasi ke skala 0 hingga 1 sesuai sifat kriteria:
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-bold text-success mb-1">Kriteria Benefit (Keuntungan)</div>
                                        <div class="text-secondary small mb-2">
                                            Nilai atribut lebih tinggi menghasilkan prioritas perbaikan lebih besar.
                                        </div>
                                        <div class="doc-formula-box py-2">
                                            r_ij = x_ij / max(x_j)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-bold text-danger mb-1">Kriteria Cost (Biaya)</div>
                                        <div class="text-secondary small mb-2">
                                            Nilai atribut lebih rendah menghasilkan prioritas perbaikan lebih besar.
                                        </div>
                                        <div class="doc-formula-box py-2">
                                            r_ij = min(x_j) / x_ij
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h4 class="text-body fw-bold mb-2">Langkah 3: Perhitungan Nilai Preferensi ($V$)</h4>
                            <p class="text-secondary small mb-2">
                                Nilai preferensi setiap tiket ($V_i$) diperoleh dari penjumlahan perkalian baris matriks ternormalisasi dengan bobot kriteria:
                            </p>
                            <div class="doc-formula-box mb-2">
                                V_i = ∑ (w_j * r_ij)  untuk j = 1 sampai n
                            </div>
                            <div class="text-secondary small">
                                Bobot setiap kriteria dinormalisasi terhadap total bobot kriteria aktif (<span class="font-monospace">w_j = bobot_j / total_bobot</span>). Tiket dengan nilai $V_i$ terbesar menempati peringkat teratas.
                            </div>
                        </div>

                        <div class="border-start border-primary border-3 ps-3 py-2 bg-surface-secondary rounded-end">
                            <div class="fw-bold text-body small">Aturan Penentu Nilai Seri (Tie-breaker)</div>
                            <div class="text-secondary small">
                                Apabila dua tiket memiliki nilai preferensi yang sama, sistem mendahulukan tiket yang dilaporkan lebih awal berdasarkan waktu pembuatan tiket (<span class="font-monospace">created_at</span>).
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 5. Kriteria Penilaian dan Pembobotan Aktif --}}
                <section id="kriteria-bobot" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-indigo-lt text-indigo rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065" />
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                </svg>
                            </span>
                            <h3 class="card-title m-0">5. Kriteria &amp; Subkriteria Aktif</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-body leading-relaxed mb-3">
                            Kriteria di bawah ini tersimpan pada basis data dan digunakan langsung dalam kalkulasi antrean SAW:
                        </p>

                        <div class="d-flex flex-column gap-3">
                            @forelse ($criterias as $criteria)
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary-lt font-monospace fs-4">{{ $criteria->code }}</span>
                                            <span class="fw-bold text-body fs-3">{{ $criteria->name }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $criteria->type->value === 'benefit' ? 'bg-success-lt' : 'bg-danger-lt' }}">
                                                Atribut: {{ ucfirst($criteria->type->value ?? $criteria->type) }}
                                            </span>
                                            <span class="badge bg-primary-lt font-monospace">
                                                Bobot: {{ (float) $criteria->weight * 100 }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-secondary small mb-2">
                                        Metode Input: <span class="fw-medium text-body">{{ $criteria->input_type->value === 'range' ? 'Rentang Angka Numerik' : 'Pilihan Kategorikal' }}</span>
                                    </div>

                                    @if ($criteria->subCriterias->isNotEmpty())
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered m-0">
                                                <thead class="bg-surface-secondary">
                                                    <tr>
                                                        <th>Deskripsi Subkriteria</th>
                                                        @if ($criteria->input_type->value === 'range')
                                                            <th style="width: 30%;">Rentang Nilai</th>
                                                        @endif
                                                        <th style="width: 20%;" class="text-center">Nilai Crisp</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($criteria->subCriterias as $sub)
                                                        <tr>
                                                            <td>{{ $sub->description }}</td>
                                                            @if ($criteria->input_type->value === 'range')
                                                                <td class="font-monospace small">
                                                                    @if ($sub->min_value !== null && $sub->max_value !== null)
                                                                        {{ number_format((float) $sub->min_value, 0, ',', '.') }} – {{ number_format((float) $sub->max_value, 0, ',', '.') }}
                                                                    @elseif ($sub->min_value !== null)
                                                                        &ge; {{ number_format((float) $sub->min_value, 0, ',', '.') }}
                                                                    @elseif ($sub->max_value !== null)
                                                                        &le; {{ number_format((float) $sub->max_value, 0, ',', '.') }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            @endif
                                                            <td class="text-center font-monospace fw-bold">
                                                                {{ $sub->crisp_value }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-secondary small py-2">
                                    Belum ada kriteria yang tersimpan di dalam sistem.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- 6. Pelaporan & Ekspor Data --}}
                <section id="laporan-ekspor" class="card shadow-sm doc-section">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-azure-lt text-azure rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M9 15l2 2l4 -4" />
                                </svg>
                            </span>
                            <h3 class="card-title m-0">6. Pelaporan &amp; Ekspor Data</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-body leading-relaxed mb-3">
                            Sistem menyediakan berkas laporan cetak dan unduhan data tabel untuk keperluan arsip administrasi sarana prasarana:
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-red-lt font-monospace">Format PDF</span>
                                    </div>
                                    <p class="text-secondary small mb-0">
                                        Menghasilkan lembar laporan resmi siap cetak berisi daftar tiket, pelapor, ruangan aset, status perbaikan, dan skor SAW.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-green-lt font-monospace">Format Excel</span>
                                    </div>
                                    <p class="text-secondary small mb-0">
                                        Menyediakan data terstruktur dalam berkas spreadsheet untuk pengolahan anggaran dan evaluasi tahunan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </article>
        </div>
    </div>
@endsection
