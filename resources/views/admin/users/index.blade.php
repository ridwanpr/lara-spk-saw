@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Data Pengguna
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Tambah Pengguna
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
            <h3 class="card-title">Daftar Pengguna</h3>
            <div class="card-actions">
                <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2 align-items-center">
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
                            class="form-control form-control-sm" placeholder="Cari nama atau email...">
                    </div>

                    <select name="role_id" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ $roleId === $role->id ? 'selected' : '' }}>
                                {{ $role->name instanceof \App\Enums\RoleName ? $role->name->label() : $role->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                    @if ($search !== '' || $roleId > 0)
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-ghost-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Terdaftar Pada</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm me-2 bg-secondary-lt fw-bold">
                                        {{ strtoupper(substr($item->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <div class="fw-medium">{{ $item->name }}</div>
                                        @if ($item->id === auth()->id())
                                            <div class="small text-secondary">(Akun Anda)</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary">
                                {{ $item->email }}
                            </td>
                            <td>
                                @php
                                    $roleKey =
                                        $item->role?->name instanceof \App\Enums\RoleName
                                            ? $item->role->name->value
                                            : (string) $item->role?->name;
                                    $roleLabel =
                                        $item->role?->name instanceof \App\Enums\RoleName
                                            ? $item->role->name->label()
                                            : $item->role?->name ?? '-';
                                    $badgeClass = match ($roleKey) {
                                        'admin' => 'bg-blue-lt',
                                        'inspector' => 'bg-teal-lt',
                                        'staff' => 'bg-secondary-lt',
                                        default => 'bg-secondary-lt',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td class="text-secondary small">
                                {{ $item->created_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.users.edit', $item) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Ubah
                                    </a>

                                    @if ($item->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $item->name }}?');"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">
                                Tidak ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-secondary">
                    Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} dari
                    {{ $users->total() }} pengguna
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
