@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.criterias.index') }}">Data Kriteria</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Tambah</a></li>
                </ol>
                <h2 class="page-title">Tambah Kriteria Baru</h2>
                <div class="text-secondary small mt-1">
                    Tambahkan kriteria baru ke dalam sistem pendukung keputusan.
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
                <form action="{{ route('admin.criterias.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{-- Nama Kriteria --}}
                        <div class="mb-3">
                            <label class="form-label required">Nama Kriteria</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" autofocus required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kode Kriteria --}}
                        <div class="mb-3">
                            <label class="form-label required">Kode Kriteria</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                placeholder="Contoh: C1, C2, C3" value="{{ old('code') }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Atribut (Type) --}}
                        <div class="mb-3">
                            <label class="form-label required">Atribut</label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                <option value="" disabled {{ old('type') ? '' : 'selected' }}>Pilih Atribut</option>
                                <option value="benefit" {{ old('type') === 'benefit' ? 'selected' : '' }}>Benefit</option>
                                <option value="cost" {{ old('type') === 'cost' ? 'selected' : '' }}>Cost</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Bobot (Weight) --}}
                        <div class="mb-3">
                            <label class="form-label required">Bobot</label>
                            <input type="number" step="0.01" min="0.01" max="1" name="weight"
                                class="form-control @error('weight') is-invalid @enderror" placeholder="Contoh: 0.25"
                                value="{{ old('weight') }}" required>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipe Penilaian (Input Type) --}}
                        <div class="mb-3">
                            <label class="form-label required">Tipe Penilaian</label>
                            <select class="form-select @error('input_type') is-invalid @enderror" name="input_type"
                                required>
                                <option value="" disabled {{ old('input_type') ? '' : 'selected' }}>Pilih Tipe
                                </option>
                                <option value="range" {{ old('input_type') === 'range' ? 'selected' : '' }}>Rentang Nilai
                                </option>
                                <option value="categorical" {{ old('input_type') === 'categorical' ? 'selected' : '' }}>
                                    Kategorikal</option>
                            </select>
                            @error('input_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.criterias.index') }}" class="btn btn-link link-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Simpan Kriteria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
