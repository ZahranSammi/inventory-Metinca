@extends('layouts.app')

@section('title', 'Riwayat Pengajuan Peminjaman')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-clock-history me-2"></i>Riwayat Pengajuan Peminjaman</h3>
                <p class="text-subtitle text-muted mb-0">Arsip riwayat seluruh transaksi peminjaman barang Anda yang telah selesai dikembalikan atau ditolak.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Riwayat Pengajuan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Arsip Peminjaman Saya</h5>
            <a href="{{ route('inventory.peminjaman.tracking') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-geo-alt me-1"></i> Lihat Pengajuan Aktif
            </a>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Selesai / Kembali</th>
                            <th>Status Akhir</th>
                            <th>Kondisi Saat Kembali</th>
                            <th>Petugas / Verifikator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayats as $r)
                            <tr>
                                <td><span class="badge bg-light-primary text-primary font-monospace">{{ $r->kode_transaksi }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $r->barang->nama }}</div>
                                    <small class="text-muted">{{ $r->jumlah }} Unit &bull; {{ $r->keperluan }}</small>
                                </td>
                                <td>{{ $r->tgl_pinjam->format('d M Y') }}</td>
                                <td>{{ $r->tgl_kembali_aktual ? $r->tgl_kembali_aktual->format('d M Y H:i') : ($r->updated_at->format('d M Y')) }}</td>
                                <td>
                                    @if($r->status === 'dikembalikan')
                                        <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Selesai</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->kondisi_kembali)
                                        <span class="badge bg-light-{{ $r->kondisi_kembali === 'baik' ? 'success' : 'warning' }} text-dark">
                                            {{ ucfirst($r->kondisi_kembali) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="d-block"><strong>Manager:</strong> {{ $r->verifikator?->name ?? '-' }}</small>
                                    <small class="d-block"><strong>Accounting:</strong> {{ $r->staffAccounting?->name ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i> Belum ada riwayat peminjaman yang selesai atau ditolak.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $riwayats->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
