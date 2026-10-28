@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Data Aset</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Ubah</a></li>
                </ol>
                <h2 class="page-title">
                    Ubah Data Aset
                </h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Aset: {{ $asset->name }} ({{ $asset->code }})</h3>
                </div>
                <form action="{{ route('admin.assets.update', $asset) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Kode Aset (Barcode / Nomor Seri)</label>
                                <input type="text" name="code"
                                    class="form-control text-uppercase @error('code') is-invalid @enderror"
                                    placeholder="Contoh: AST-PC-001" value="{{ (string) old('code', $asset->code) }}"
                                    required autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <small class="form-hint">Harus unik dan akan otomatis dikapitalisasi.</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Nama Aset / Barang</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Contoh: PC Client Lab 1" value="{{ (string) old('name', $asset->name) }}"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Kategori Aset</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) old('category_id', $asset->category_id) === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Ruangan / Lokasi</label>
                                <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Lokasi Ruangan --</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}"
                                            {{ (string) old('room_id', $asset->room_id) === (string) $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} ({{ $room->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Tahun Pengadaan</label>
                                <input type="number" name="procurement_year"
                                    class="form-control @error('procurement_year') is-invalid @enderror" min="1990"
                                    max="{{ (int) date('Y') + 1 }}" placeholder="Contoh: {{ date('Y') }}"
                                    value="{{ (string) old('procurement_year', $asset->procurement_year) }}" required>
                                @error('procurement_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Kondisi Aset</label>
                                <select name="condition" class="form-select @error('condition') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Kondisi --</option>
                                    @foreach ($conditions as $cond)
                                        @php
                                            $currentConditionValue =
                                                $asset->condition instanceof \App\Enums\AssetCondition
                                                    ? $asset->condition->value
                                                    : (string) $asset->condition;
                                        @endphp
                                        <option value="{{ $cond->value }}"
                                            {{ (string) old('condition', $currentConditionValue) === $cond->value ? 'selected' : '' }}>
                                            {{ $cond->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.assets.index') }}" class="btn btn-link link-secondary">
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
