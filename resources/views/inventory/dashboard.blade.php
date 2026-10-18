@extends('layouts.app')

@section('title', 'Dashboard Inventaris')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="mb-0 text-primary"><i class="bi bi-box-seam me-2"></i>Inventaris Operasional</h3>
                    <span class="badge bg-light-primary text-primary fs-6">{{ $user->role_label ?? 'Karyawan' }}</span>
                </div>
                <p class="text-subtitle text-muted mb-0">Selamat datang, <strong>{{ $user->name ?? 'Pengguna' }}</strong> ({{ $user->department ?? 'Operasional' }})</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home.main') }}"><i class="bi bi-house-door me-1"></i>Portal Metinca</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard Inventaris</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ROLE SPECIFIC DASHBOARDS --}}
    @if($user && $user->isKaryawan())
        {{-- ==================== DASHBOARD KARYAWAN ==================== --}}
        <div class="row mb-4">
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-warning me-3">
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Pengajuan Menunggu</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalDiajukan }} <span class="fs-6 text-muted fw-normal">Tiket</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-primary me-3">
                            <i class="bi bi-box-arrow-right fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Sedang Dipinjam</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalDipinjam }} <span class="fs-6 text-muted fw-normal">Barang</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-success me-3">
                            <i class="bi bi-check2-all fs-2 text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Peminjaman Selesai</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalSelesai }} <span class="fs-6 text-muted fw-normal">Riwayat</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Status Pengajuan Peminjaman Aktif Saya</h5>
                        <a href="{{ route('inventory.peminjaman.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Ajukan Pinjam Baru
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Barang</th>
                                        <th>Tgl Rencana Kembali</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($peminjamanAktif as $p)
                                        <tr>
                                            <td class="font-monospace fw-bold text-primary">{{ $p->kode_transaksi }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $p->barang->nama }}</div>
                                                <small class="text-muted">{{ $p->jumlah }} Unit &bull; {{ $p->barang->lokasi }}</small>
                                            </td>
                                            <td>{{ $p->tgl_kembali_rencana ? $p->tgl_kembali_rencana->format('d M Y') : '-' }}</td>
                                            <td>
                                                @php $b = $p->status_badge; @endphp
                                                <span class="badge bg-{{ $b['color'] }}">
                                                    <i class="bi {{ $b['icon'] }} me-1"></i>{{ $b['label'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('inventory.peminjaman.tracking') }}" class="btn btn-sm btn-outline-primary" title="Tracking">
                                                    <i class="bi bi-geo-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i> Belum ada pengajuan peminjaman aktif saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Menu Cepat Karyawan</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <a href="{{ route('inventory.peminjaman.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Pengajuan Peminjaman</strong>
                                <small class="text-muted">Formulir pinjam barang & perkakas</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.peminjaman.tracking') }}" class="btn btn-outline-info d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Tracking Pengajuan</strong>
                                <small class="text-muted">Lihat posisi verifikasi & serah terima</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.barang.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Katalog Data Barang</strong>
                                <small class="text-muted">Cek spesifikasi & stok alat pabrik</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($user && $user->isAccounting())
        {{-- ==================== DASHBOARD STAFF ACCOUNTING ==================== --}}
        <div class="row mb-4">
            <div class="col-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-primary me-3">
                            <i class="bi bi-boxes fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Unit Barang</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalBarang }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-warning me-3">
                            <i class="bi bi-box-seam fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Siap Diserahkan</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalMenungguPenyerahan }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-info me-3">
                            <i class="bi bi-truck fs-2 text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Sedang di Lapangan</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalSedangDipinjam }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-danger me-3">
                            <i class="bi bi-tools fs-2 text-danger"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Dalam Maintenance</h6>
                            <h3 class="mb-0 fw-bold">{{ $barangMaintenance }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Tiket Peminjaman yang Telah Disetujui Manager</h5>
                            <small class="text-muted">Periksa ketersediaan barang dan lakukan serah terima fisik kepada pemohon</small>
                        </div>
                        <a href="{{ route('inventory.peminjaman.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Pemohon</th>
                                        <th>Barang</th>
                                        <th>Stok Gudang</th>
                                        <th class="text-center">Aksi Serah Terima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menungguPenyerahan as $p)
                                        <tr>
                                            <td class="font-monospace fw-bold text-primary">{{ $p->kode_transaksi }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $p->user->name }}</div>
                                                <small class="text-muted">{{ $p->user->department }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $p->barang->nama }}</div>
                                                <small class="text-muted">Jumlah: {{ $p->jumlah }} Unit</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $p->barang->stok_tersedia >= $p->jumlah ? 'success' : 'danger' }}">
                                                    Tersedia: {{ $p->barang->stok_tersedia }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('inventory.peminjaman.serahkan', $p->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Serahkan barang ini dan kirimkan informasi peminjaman?')">
                                                        <i class="bi bi-box-arrow-right me-1"></i> Serahkan Barang
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-check2-circle fs-2 d-block mb-2 text-success"></i> Semua tiket yang disetujui telah diserahterimakan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Aksi Cepat Staff Accounting</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <a href="{{ route('inventory.barang.ketersediaan') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Pemeriksaan Ketersediaan</strong>
                                <small class="text-muted">Cek stok fisik & update kondisi alat</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.peminjaman.index') }}" class="btn btn-outline-info d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Peminjaman & Informasi</strong>
                                <small class="text-muted">Kirim info peminjaman & kelola pengembalian</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.tracking.index') }}" class="btn btn-outline-warning d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Tracking Barang</strong>
                                <small class="text-muted">Pantau posisi barang bergerak</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($user && $user->isManager())
        {{-- ==================== DASHBOARD MANAGER ==================== --}}
        <div class="row mb-4">
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-warning me-3">
                            <i class="bi bi-shield-exclamation fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Menunggu Verifikasi Anda</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalMenungguVerifikasi }} <span class="fs-6 text-muted fw-normal">Tiket</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-success me-3">
                            <i class="bi bi-check2-circle fs-2 text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Disetujui Bulan Ini</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalDisetujuiBulanIni }} <span class="fs-6 text-muted fw-normal">Tiket</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-primary me-3">
                            <i class="bi bi-file-earmark-bar-graph fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Status Laporan Terakhir</h6>
                            <h4 class="mb-0 fw-bold fs-6">
                                <span class="badge bg-{{ $laporanTerbaru?->status_badge['color'] ?? 'secondary' }}">
                                    {{ $laporanTerbaru?->status_badge['label'] ?? 'Belum ada laporan' }}
                                </span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Antrean Verifikasi Peminjaman Barang</h5>
                            <small class="text-muted">Persetujuan manajerial untuk penggunaan fasilitas & perkakas operasional</small>
                        </div>
                        <a href="{{ route('inventory.peminjaman.verifikasi') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Pemohon</th>
                                        <th>Barang & Jumlah</th>
                                        <th>Keperluan</th>
                                        <th class="text-center">Aksi Verifikasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menungguVerifikasi as $p)
                                        <tr>
                                            <td class="font-monospace fw-bold text-primary">{{ $p->kode_transaksi }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $p->user->name }}</div>
                                                <small class="text-muted">{{ $p->user->department }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $p->barang->nama }}</div>
                                                <small class="text-muted">{{ $p->jumlah }} Unit</small>
                                            </td>
                                            <td><small class="text-truncate d-inline-block" style="max-width: 200px;">{{ $p->keperluan }}</small></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('inventory.peminjaman.verifikasi.action', $p->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-success" title="Setujui">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('inventory.peminjaman.verifikasi.action', $p->id) }}" method="POST" class="d-inline ms-1">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-danger" title="Tolak" onclick="return confirm('Tolak pengajuan peminjaman ini?')">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i> Tidak ada pengajuan peminjaman yang menunggu verifikasi saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Menu Manajerial</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <a href="{{ route('inventory.peminjaman.verifikasi') }}" class="btn btn-outline-warning d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Verifikasi Peminjaman</strong>
                                <small class="text-muted">Review dan setujui izin alat</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.laporan.operasional') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Laporan Barang Operasional</strong>
                                <small class="text-muted">Susun laporan untuk Direktur SDM</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.tracking.index') }}" class="btn btn-outline-info d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Tracking Barang</strong>
                                <small class="text-muted">Monitoring aset bergerak di pabrik</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($user && $user->isDirektur())
        {{-- ==================== DASHBOARD DIREKTUR SDM ==================== --}}
        <div class="row mb-4">
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-warning me-3">
                            <i class="bi bi-file-earmark-text fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Laporan Menunggu Review</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalLaporanMenunggu }} <span class="fs-6 text-muted fw-normal">Dokumen</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-success me-3">
                            <i class="bi bi-archive fs-2 text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Laporan Telah Diarsipkan</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalLaporanDiarsipkan }} <span class="fs-6 text-muted fw-normal">Arsip</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar avatar-xl bg-light-primary me-3">
                            <i class="bi bi-radar fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Master Barang</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalBarang }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Laporan Operasional yang Memerlukan Pemeriksaan</h5>
                            <small class="text-muted">Tinjau laporan berkala dari manajer sebelum dilakukan pengarsipan resmi</small>
                        </div>
                        <a href="{{ route('inventory.laporan.pemeriksaan') }}" class="btn btn-sm btn-outline-primary">Buka Halaman Arsip</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nomor Laporan</th>
                                        <th>Judul & Periode</th>
                                        <th>Disusun Oleh</th>
                                        <th class="text-center">Aksi Pemeriksaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($laporanMenungguReview as $lap)
                                        <tr>
                                            <td class="font-monospace fw-bold text-primary">{{ $lap->nomor_laporan }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $lap->judul }}</div>
                                                <small class="badge bg-light-info text-info">{{ $lap->periode }}</small>
                                            </td>
                                            <td>{{ $lap->penyusun->name }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('inventory.laporan.pemeriksaan') }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye me-1"></i> Periksa & Arsipkan
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2 text-success"></i> Tidak ada laporan operasional yang menunggu pemeriksaan saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Menu Eksekutif Direktur SDM</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <a href="{{ route('inventory.laporan.pemeriksaan') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Pemeriksaan & Arsip Laporan</strong>
                                <small class="text-muted">Evaluasi & simpan dokumen audit aset</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.tracking.index') }}" class="btn btn-outline-success d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Tracking Barang</strong>
                                <small class="text-muted">Monitoring aset bergerak di pabrik</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('inventory.barang.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-between p-3 text-start">
                            <div>
                                <strong class="d-block">Katalog Data Barang</strong>
                                <small class="text-muted">Master database seluruh aset perusahaan</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Log Tracking Pergerakan Barang Terkini (Umum) --}}
    <div class="card shadow-sm border-0 mt-2">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-activity me-2 text-info"></i>Aktivitas Tracking Barang Terkini</h5>
            <a href="{{ route('inventory.tracking.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua Pergerakan &rarr;</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Barang</th>
                            <th>Status Posisi</th>
                            <th>Lokasi Terkini</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTrackings as $trk)
                            <tr>
                                <td class="text-muted small">{{ $trk->created_at->diffForHumans() }}</td>
                                <td class="fw-bold">{{ $trk->barang->nama }}</td>
                                <td>
                                    <span class="badge bg-light-primary text-primary">{{ $trk->status_posisi }}</span>
                                </td>
                                <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $trk->lokasi_sekarang }}</td>
                                <td class="small text-muted">{{ $trk->keterangan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat aktivitas tracking.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
