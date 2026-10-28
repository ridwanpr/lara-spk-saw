@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.asset-categories.index') }}">Kategori Aset</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Ubah</a></li>
                </ol>
                <h2 class="page-title">
                    Ubah Kategori Aset
                </h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Kategori: {{ $category->name }}</h3>
                </div>
                <form action="{{ route('admin.asset-categories.update', $category) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Kategori</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Elektronik, Furniture"
                                value="{{ (string) old('name', $category->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Jelaskan jenis atau cakupan barang dalam kategori ini..." required>{{ (string) old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-link link-secondary">
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
