@extends('layouts.app')

@section('title', 'Verifikasi Peminjaman Barang')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="mb-1 text-primary"><i class="bi bi-shield-check me-2"></i>Verifikasi Peminjaman Barang</h3>
                <p class="text-subtitle text-muted mb-0">Halaman verifikasi dan persetujuan manajerial untuk permohonan penggunaan inventaris dan perkakas operasional.</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verifikasi Peminjaman</li>
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

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Daftar Permohonan Menunggu Keputusan Manager</h5>
                <small class="text-muted">Total: {{ $peminjamans->total() }} permohonan menunggu tindakan</small>
            </div>
            <a href="{{ route('inventory.tracking.index') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-radar me-1"></i> Cek Tracking Seluruh Barang
            </a>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Pemohon (Karyawan)</th>
                            <th>Barang yang Diminta</th>
                            <th>Durasi Peminjaman</th>
                            <th>Alasan / Keperluan</th>
                            <th class="text-center" style="min-width: 170px;">Keputusan Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $p)
                            <tr>
                                <td>
                                    <span class="badge bg-light-primary text-primary font-monospace">{{ $p->kode_transaksi }}</span>
                                    <div class="small text-muted">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $p->user->name }}</div>
                                    <small class="text-muted">{{ $p->user->department }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $p->barang->nama }}</div>
                                    <small class="text-muted">Jumlah: {{ $p->jumlah }} Unit &bull; Tersedia: {{ $p->barang->stok_tersedia }}</small>
                                </td>
                                <td>
                                    <div class="small"><strong>Mulai:</strong> {{ $p->tgl_pinjam->format('d M Y') }}</div>
                                    <div class="small"><strong>Selesai:</strong> {{ $p->tgl_kembali_rencana ? $p->tgl_kembali_rencana->format('d M Y') : '-' }}</div>
                                </td>
                                <td>
                                    <p class="small mb-0 text-break">{{ $p->keperluan }}</p>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalApprove{{ $p->id }}" title="Setujui">
                                        <i class="bi bi-check-circle me-1"></i> Setuju
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalReject{{ $p->id }}" title="Tolak">
                                        <i class="bi bi-x-circle me-1"></i> Tolak
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Setuju --}}
                            <div class="modal fade text-start" id="modalApprove{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-check-circle text-success me-2"></i>Persetujuan Peminjaman Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('inventory.peminjaman.verifikasi.action', $p->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin menyetujui peminjaman <strong>{{ $p->barang->nama }}</strong> ({{ $p->jumlah }} Unit) untuk karyawan <strong>{{ $p->user->name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Catatan Manager (Opsional)</label>
                                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Disetujui. Perhatikan SOP keselamatan kerja."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Ya, Setujui Permohonan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Tolak --}}
                            <div class="modal fade text-start" id="modalReject{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Tolak Peminjaman Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('inventory.peminjaman.verifikasi.action', $p->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <div class="modal-body">
                                                <p>Tolak permohonan peminjaman <strong>{{ $p->kode_transaksi }}</strong> oleh <strong>{{ $p->user->name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Tulis alasan penolakan agar pemohon memahami alasannya..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Tolak Permohonan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i> Semua pengajuan peminjaman telah diverifikasi!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $peminjamans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
