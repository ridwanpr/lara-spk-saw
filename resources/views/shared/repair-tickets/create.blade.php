@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('repair-tickets.index') }}">Laporan Aset Rusak</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Tambah</a></li>
                </ol>
                <h2 class="page-title">Tambah Tiket Baru</h2>
                <div class="text-secondary small mt-1">
                    Buat laporan kerusakan aset untuk dilakukan perbaikan.
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Formulir Tambah Kriteria</h3>
                </div>
                <form action="{{ route('repair-tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-important alert-danger alert-dismissible fade show" role="alert">
                                <div>{{ session('error') }}</div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        {{-- Pilih Aset --}}
                        <div class="mb-3">
                            <label class="form-label required">Pilih Aset</label>
                            <select class="form-select @error('asset_id') is-invalid @enderror" name="asset_id" required>
                                <option value="">Pilih aset...</option>
                                @forelse ($assets as $asset)
                                    <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>
                                        {{ $asset->name }}
                                    </option>
                                @empty
                                    <option value="">Data aset tidak ditemukan</option>
                                @endforelse
                            </select>
                            @error('asset_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Deskripsi Kerusakan --}}
                        <div class="mb-3">
                            <label class="form-label required">Deskripsi Kerusakan</label>
                            <textarea name="damage_description" rows="4"
                                class="form-control @error('damage_description') is-invalid @enderror" placeholder="Masukkan deskripsi kerusakan..."
                                required>{{ old('damage_description') }}</textarea>
                            @error('damage_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Foto --}}
                        <div class="mb-3">
                            <label class="form-label">Bukti Foto</label>
                            <input type="file" name="damage_photo"
                                class="form-control @error('damage_photo') is-invalid @enderror">
                            @error('damage_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('repair-tickets.index') }}" class="btn btn-link link-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
