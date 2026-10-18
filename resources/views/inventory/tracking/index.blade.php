@extends('layouts.app')

@section('title', 'Tracking Barang & Lokasi')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="mb-1 text-primary"><i class="bi bi-radar me-2"></i>Tracking Barang & Lokasi Operasional</h3>
                <p class="text-subtitle text-muted mb-0">Pemantauan lokasi fisik barang bergerak, status peminjaman di lapangan, dan riwayat pergerakan inventaris.</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tracking Barang</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Kartu Barang yang Sedang di Luar / Dipinjam --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-broadcast text-danger me-2"></i>Barang yang Sedang Beroperasi di Lapangan</h5>
            <span class="badge bg-warning text-dark">{{ $barangDiLuar->count() }} Unit Sedang Aktif di Luar</span>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                @forelse($barangDiLuar as $item)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="border rounded-3 p-3 bg-light-warning h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary font-monospace">{{ $item->barang->kode }}</span>
                                <span class="badge bg-danger">Sedang Dipinjam</span>
                            </div>
                            <h6 class="mb-1 text-dark fw-bold">{{ $item->barang->nama }}</h6>
                            <small class="text-muted d-block mb-2">Jumlah: {{ $item->jumlah }} Unit &bull; Tiket: {{ $item->kode_transaksi }}</small>
                            <hr class="my-2 border-secondary opacity-25">
                            <div class="small">
                                <div><i class="bi bi-person me-1 text-primary"></i><strong>Pemegang:</strong> {{ $item->user->name }}</div>
                                <div><i class="bi bi-building me-1 text-info"></i><strong>Divisi:</strong> {{ $item->user->department }}</div>
                                <div><i class="bi bi-calendar-check me-1 text-success"></i><strong>Rencana Kembali:</strong> {{ $item->tgl_kembali_rencana ? $item->tgl_kembali_rencana->format('d M Y') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-3 text-muted">
                        <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                        Semua barang operasional saat ini berada di tempat penyimpanan / gudang.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Log Tracking --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Riwayat Pergerakan & Status Posisi Barang</h5>
        </div>

        <div class="card-body pt-3">
            {{-- Filter Pencarian --}}
            <form action="{{ route('inventory.tracking.index') }}" method="GET" class="row g-2 mb-4">
                <div class="col-12 col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama barang, kode, status posisi, atau lokasi terkini..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Pencatatan</th>
                            <th>Barang</th>
                            <th>Status Posisi</th>
                            <th>Lokasi Terkini</th>
                            <th>Peminjam / Petugas</th>
                            <th>Keterangan Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trackings as $trk)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $trk->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $trk->created_at->format('H:i') }} ({{ $trk->created_at->diffForHumans() }})</small>
                                </td>
                                <td>
                                    <span class="badge bg-light-primary text-primary font-monospace">{{ $trk->barang->kode }}</span>
                                    <div class="fw-bold">{{ $trk->barang->nama }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-white">{{ $trk->status_posisi }}</span>
                                </td>
                                <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $trk->lokasi_sekarang }}</td>
                                <td>
                                    @if($trk->peminjaman)
                                        <div class="small fw-bold">{{ $trk->peminjaman->user->name }}</div>
                                        <small class="text-muted">Pemohon ({{ $trk->peminjaman->user->department }})</small>
                                    @elseif($trk->petugas)
                                        <div class="small fw-bold">{{ $trk->petugas->name }}</div>
                                        <small class="text-muted">Staff Pengelola</small>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $trk->keterangan ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pergerakan tracking yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $trackings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
