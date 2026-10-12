@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Data Ruangan</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Tambah</a></li>
                </ol>
                <h2 class="page-title">
                    Tambah Ruangan Baru
                </h2>
                <div class="text-secondary small mt-1">
                    Daftarkan ruangan atau lokasi baru untuk penempatan aset sekolah.
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Formulir Data Ruangan</h3>
                </div>
                <form action="{{ route('admin.rooms.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Kode Ruangan</label>
                            <input type="text" name="code"
                                class="form-control text-uppercase @error('code') is-invalid @enderror"
                                placeholder="Contoh: LAB-KOMP-1, KLS-7A" value="{{ (string) old('code') }}" required
                                autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-hint">Kode harus unik dan akan otomatis dikonversi ke huruf kapital.</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Nama Ruangan</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Laboratorium Komputer 1" value="{{ (string) old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-link link-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Simpan Ruangan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
