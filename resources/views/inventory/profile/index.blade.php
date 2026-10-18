@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-person-circle me-2"></i>Profil Saya</h3>
                <p class="text-subtitle text-muted mb-0">Informasi akun pengguna dan identitas kepegawaian ERP Metinca.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil Saya</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="avatar avatar-2xl mx-auto mb-3">
                    <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 100px; height: 100px;">
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                <div>
                    <span class="badge bg-primary fs-6">{{ $user->role_label }}</span>
                </div>
                <hr class="my-3">
                <div class="text-start small">
                    <div class="mb-2"><i class="bi bi-building me-2 text-primary"></i><strong>Divisi:</strong> {{ $user->department ?? '-' }}</div>
                    <div class="mb-2"><i class="bi bi-person-badge me-2 text-info"></i><strong>ID Karyawan:</strong> {{ $user->employee_id ?? '-' }}</div>
                    <div><i class="bi bi-telephone me-2 text-success"></i><strong>Telepon:</strong> {{ $user->phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Ubah Data Profil</h5>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('inventory.profile.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-bold">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Alamat Email</label>
                                <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                                <small class="text-muted">Email akun terhubung dengan login sistem.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Nomor Kontak / WhatsApp</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 0812-xxxx-xxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Departemen / Divisi</label>
                                <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
