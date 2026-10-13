@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.criterias.index') }}">Data Kriteria</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="#">Detail</a></li>
                </ol>
                <h2 class="page-title">Detail Kriteria</h2>
                <div class="text-secondary small mt-1">
                    Edit kriteria dan manajemen sub-kriteria.
                </div>
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


    <div class="row">
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Formulir Edit Kriteria</h3>
                </div>
                <form action="{{ route('admin.criterias.update', $criteria->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- Nama Kriteria --}}
                        <div class="mb-3">
                            <label class="form-label required">Nama Kriteria</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ $criteria->name }}" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kode Kriteria --}}
                        <div class="mb-3">
                            <label class="form-label required">Kode Kriteria</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                placeholder="Contoh: C1, C2, C3" value="{{ (string) $criteria->code }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Atribut (Type) --}}
                        <div class="mb-3">
                            <label class="form-label required">Atribut</label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                <option value="benefit" {{ $criteria->type->value === 'benefit' ? 'selected' : '' }}>
                                    Benefit</option>
                                <option value="cost" {{ $criteria->type->value === 'cost' ? 'selected' : '' }}>Cost
                                </option>
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
                                value="{{ $criteria->weight }}" required>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipe Penilaian (Input Type) --}}
                        <div class="mb-3">
                            <label class="form-label required">Tipe Penilaian</label>
                            <select class="form-select @error('input_type') is-invalid @enderror" name="input_type"
                                required>

                                <option value="range" {{ $criteria->input_type->value === 'range' ? 'selected' : '' }}>
                                    Rentang Nilai
                                </option>
                                <option value="categorical"
                                    {{ $criteria->input_type->value === 'categorical' ? 'selected' : '' }}>
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
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Sub Kriteria</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#create-subcriteria-modal">
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    @forelse ($criteria->subCriterias as $subCriteria)
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div class="me-3">
                                <div class="fw-semibold">
                                    {{ $subCriteria->description }}
                                </div>

                                @if ($criteria->input_type->value === 'range')
                                    <div class="text-secondary small mt-1">
                                        Rentang: {{ $subCriteria->min_value }} &ndash; {{ $subCriteria->max_value }}
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-lt">
                                    Crisp: {{ $subCriteria->crisp_value }}
                                </span>

                                <div class="btn-list flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-secondary small">
                            Belum ada sub kriteria yang ditambahkan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @include('admin.criterias.subcriteria.create')
@endsection
