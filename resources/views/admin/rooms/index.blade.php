@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Master Aset
                </div>
                <h2 class="page-title">
                    Data Ruangan
                </h2>
                <div class="text-secondary small mt-1">
                    Kelola data ruangan dan lokasi penempatan aset sekolah.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <img src="{{ asset('assets/icon/plus.svg') }}" alt="Tambah" class="icon" width="20"
                        height="20">
                    Tambah Ruangan
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
            <h3 class="card-title">Daftar Ruangan</h3>
            <div class="card-actions">
                <form action="{{ route('admin.rooms.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <div class="input-icon">
                        <span class="input-icon-addon h-100">
                            <img src="{{ asset('assets/icon/search.svg') }}" alt="Cari" class="icon icon-sm"
                                width="16" height="16">
                        </span>
                        <input type="text" name="search" value="{{ (string) $search }}"
                            class="form-control form-control-sm" placeholder="Cari kode atau nama ruangan...">
                    </div>

                    <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-sm btn-ghost-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Kode Ruangan</th>
                        <th>Nama Ruangan</th>
                        <th style="width: 15%;">Jumlah Aset</th>
                        <th style="width: 20%;">Terakhir Diperbarui</th>
                        <th class="text-end" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $item)
                        <tr>
                            <td>
                                <span class="badge bg-secondary-lt text-dark fw-monospace">
                                    {{ $item->code }}
                                </span>
                            </td>
                            <td class="font-weight-medium text-dark">
                                {{ $item->name }}
                            </td>
                            <td class="text-secondary">
                                <span class="badge bg-blue-lt">
                                    {{ $item->assets_count }} aset
                                </span>
                            </td>
                            <td class="text-secondary small">
                                {{ $item->updated_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.rooms.edit', $item) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Ubah
                                    </a>

                                    <form action="{{ route('admin.rooms.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan {{ $item->name }}?');"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            {{ $item->assets_count > 0 ? 'disabled title="Tidak dapat dihapus karena masih ada aset"' : '' }}>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">
                                Tidak ada data ruangan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rooms->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-secondary">
                    Menampilkan {{ $rooms->firstItem() ?? 0 }} sampai {{ $rooms->lastItem() ?? 0 }} dari
                    {{ $rooms->total() }} ruangan
                </div>
                <div>
                    {{ $rooms->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
