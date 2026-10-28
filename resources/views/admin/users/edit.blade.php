@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Data Pengguna</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Ubah</a></li>
                </ol>
                <h2 class="page-title">
                    Ubah Data Pengguna
                </h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Pengguna: {{ $user->name }}</h3>
                </div>
                <form action="{{ route('admin.users.update', $user) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Budi Santoso, S.Pd." value="{{ (string) old('name', $user->name) }}"
                                required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                placeholder="nama@sekolah.sch.id" value="{{ (string) old('email', $user->email) }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Peran (Role)</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Peran Pengguna --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ (string) old('role_id', $user->role_id) === (string) $role->id ? 'selected' : '' }}>
                                        {{ $role->name instanceof \App\Enums\RoleName ? $role->name->label() : $role->name }}
                                        @if ($role->description)
                                            - {{ $role->description }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="hr-text text-secondary my-4">Ubah Kata Sandi (Opsional)</div>
                        <div class="small text-secondary mb-3">
                            Kosongkan bidang kata sandi di bawah jika Anda tidak ingin memperbarui kata sandi pengguna ini.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kata Sandi Baru</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Minimal 8 karakter" autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Ulangi kata sandi" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-link link-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
