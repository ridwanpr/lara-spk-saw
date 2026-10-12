@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-secondary">
                    Manajemen SPK
                </div>
                <h2 class="page-title">
                    Pengaturan SPK
                </h2>
                <div class="text-secondary small mt-1">
                    Kelola data kriteria, sub kriteria dan bobot untuk perhitungan SPK (Sistem Pendukung Keputusan).
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('admin.criterias.create') }}"
                    class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"
                        aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Tambah Kriteria
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
            <h3 class="card-title">Daftar Kriteria</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Kode</th>
                        <th>Nama</th>
                        <th style="width: 10%;">Bobot</th>
                        <th style="width: 10%;">Atribut </th>
                        <th style="width: 10%;">Tipe Penilaian</th>
                        <th class="text-end" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($criterias as $criteria)
                        <tr>
                            <td>
                                <span class="badge bg-secondary-lt font-monospace">{{ $criteria->code }}</span>
                            </td>
                            <td class="fw-medium">{{ $criteria->name }}</td>
                            <td>{{ $criteria->weight }}</td>
                            <td>
                                <span
                                    class="badge text-capitalize {{ $criteria->type->value === 'benefit' ? 'bg-success-lt' : 'bg-danger-lt' }}">
                                    {{ $criteria->type }}
                                </span>
                            </td>
                            <td class="text-capitalize">
                                {{ $criteria->input_type->value === 'range' ? 'Rentang Nilai' : 'Kategorikal' }}</td>
                            <td>
                                <div class="btn-list justify-content-end">
                                    <a href="" class="btn btn-sm btn-outline-secondary">
                                        Detail
                                    </a>

                                    <form action="{{ route('admin.criterias.destroy', $criteria->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus kriteria ini?');"
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
                            <td colspan="5" class="text-center py-4 text-secondary">
                                Data kriteria tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
