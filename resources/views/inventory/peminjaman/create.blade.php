@extends('layouts.app')

@section('title', 'Form Pengajuan Peminjaman Barang')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-file-earmark-plus me-2"></i>Pengajuan Peminjaman Barang</h3>
                <p class="text-subtitle text-muted mb-0">Formulir permohonan peminjaman mesin, alat ukur, dan perkakas operasional pabrik.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengajuan Peminjaman</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Rincian Formulir Peminjaman</h5>
                    <small class="text-muted">Pengajuan Anda akan diverifikasi oleh Manager sebelum barang diserahkan oleh Staff Accounting</small>
                </div>

                <div class="card-body pt-4">
                    <form action="{{ route('inventory.peminjaman.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            {{-- Info Pemohon --}}
                            <div class="col-md-6">
                                <label class="form-label font-bold">Nama Pemohon (Karyawan)</label>
                                <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Divisi / Departemen</label>
                                <input type="text" class="form-control bg-light" value="{{ auth()->user()->department }}" readonly>
                            </div>

                            {{-- Pilih Barang --}}
                            <div class="col-md-8">
                                <label class="form-label font-bold">Pilih Barang / Alat Operasional <span class="text-danger">*</span></label>
                                <select name="barang_id" class="form-select" required id="selectBarang">
                                    <option value="">-- Pilih Barang yang Tersedia --</option>
                                    @foreach($barangs as $b)
                                        <option value="{{ $b->id }}" {{ (request('barang_id') == $b->id || old('barang_id') == $b->id) ? 'selected' : '' }}>
                                            {{ $b->kode }} - {{ $b->nama }} (Tersedia: {{ $b->stok_tersedia }} | Lokasi: {{ $b->lokasi }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Jumlah Unit --}}
                            <div class="col-md-4">
                                <label class="form-label font-bold">Jumlah Unit <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah', 1) }}" min="1" max="10" required>
                            </div>

                            {{-- Tanggal --}}
                            <div class="col-md-6">
                                <label class="form-label font-bold">Tanggal Mulai Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_pinjam" class="form-control" value="{{ old('tgl_pinjam', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Tanggal Rencana Selesai / Kembali <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_kembali_rencana" class="form-control" value="{{ old('tgl_kembali_rencana', date('Y-m-d', strtotime('+3 days'))) }}" required>
                            </div>

                            {{-- Keperluan --}}
                            <div class="col-12">
                                <label class="form-label font-bold">Keperluan Operasional / Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                                <textarea name="keperluan" class="form-control" rows="3" placeholder="Jelaskan kebutuhan pemakaian alat, nomor order kerja (SPK), atau batch part yang sedang diproses..." required>{{ old('keperluan') }}</textarea>
                            </div>
                        </div>

                        <div class="alert alert-light-primary d-flex align-items-center gap-2 mt-4 mb-4">
                            <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                            <div class="small">
                                Setelah tombol <strong>Kirim Pengajuan</strong> ditekan, status tiket akan masuk ke <strong>Menunggu Verifikasi Manager</strong>. Anda dapat melihat perkembangannya pada menu <strong>Tracking Pengajuan</strong>.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send-fill me-1"></i> Kirim Pengajuan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
