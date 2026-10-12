@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="text-center mb-4">
        <a href="{{ url('/') }}" class="navbar-brand navbar-brand-autodark">
            <h1 class="m-0 text-primary fw-bold">SIAset</h1>
        </a>
        <p class="text-secondary small mt-1">Sistem Pendukung Keputusan Prioritas Perbaikan Aset Sekolah</p>
    </div>

    <div class="card card-md shadow-sm">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Masuk ke Akun</h2>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>{{ session('status') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div>
                            <h4 class="alert-title">Gagal Masuk</h4>
                            <div class="text-secondary">
                                {{ $errors->first() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="off" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label required">Alamat Email</label>
                    <input type="email" name="email" id="email"
                        class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id"
                        value="{{ (string) old('email') }}" required autofocus autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label required">Kata Sandi</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Kata sandi akun" required
                        autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input"
                            {{ old('remember') ? 'checked' : '' }} />
                        <span class="form-check-label">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
