@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Master Aset
                </div>
                <h2 class="page-title">
                    Data Aset
                </h2>
                <div class="text-secondary small mt-1">
                    Kelola seluruh data inventaris barang sekolah, lokasi ruangan, dan status kondisinya.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('admin.assets.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"
                        aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Tambah Aset
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
            <h3 class="card-title">Daftar Inventaris Aset</h3>
            <div class="card-actions">
                <form action="{{ route('admin.assets.index') }}" method="GET"
                    class="d-flex gap-2 align-items-center flex-wrap">
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
                            class="form-control form-control-sm" placeholder="Cari kode atau nama...">
                    </div>

                    <select name="category_id" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="room_id" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Ruangan</option>
                        @foreach ($rooms as $rm)
                            <option value="{{ $rm->id }}" {{ $roomId === $rm->id ? 'selected' : '' }}>
                                {{ $rm->name }} ({{ $rm->code }})
                            </option>
                        @endforeach
                    </select>

                    <select name="condition" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Kondisi</option>
                        @foreach ($conditions as $cond)
                            <option value="{{ $cond->value }}" {{ $condition === $cond->value ? 'selected' : '' }}>
                                {{ $cond->label() }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                    @if ($search !== '' || $categoryId > 0 || $roomId > 0 || $condition !== '')
                        <a href="{{ route('admin.assets.index') }}" class="btn btn-sm btn-ghost-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Kode Aset</th>
                        <th>Nama Aset</th>
                        <th style="width: 16%;">Kategori</th>
                        <th style="width: 16%;">Ruangan</th>
                        <th style="width: 10%;">Tahun</th>
                        <th style="width: 14%;">Kondisi</th>
                        <th class="text-end" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $item)
                        <tr>
                            <td>
                                <span class="badge bg-secondary-lt">
                                    {{ $item->code }}
                                </span>
                            </td>
                            <td class="fw-medium">
                                {{ $item->name }}
                            </td>
                            <td class="text-secondary">
                                {{ $item->category?->name ?? '-' }}
                            </td>
                            <td class="text-secondary">
                                {{ $item->room?->name ?? '-' }}
                            </td>
                            <td class="text-secondary">
                                {{ $item->procurement_year }}
                            </td>
                            <td>
                                @php
                                    $condKey =
                                        $item->condition instanceof \App\Enums\AssetCondition
                                            ? $item->condition->value
                                            : (string) $item->condition;
                                    $condLabel =
                                        $item->condition instanceof \App\Enums\AssetCondition
                                            ? $item->condition->label()
                                            : $item->condition ?? '-';
                                    $badgeClass = match ($condKey) {
                                        'good' => 'bg-green-lt',
                                        'damaged' => 'bg-danger-lt',
                                        'under_repair' => 'bg-warning-lt',
                                        default => 'bg-secondary-lt',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $condLabel }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.assets.edit', $item) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Ubah
                                    </a>

                                    <form action="{{ route('admin.assets.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset {{ $item->name }} ({{ $item->code }})?');"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                Tidak ada data aset yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assets->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-secondary">
                    Menampilkan {{ $assets->firstItem() ?? 0 }} sampai {{ $assets->lastItem() ?? 0 }} dari
                    {{ $assets->total() }} aset
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
