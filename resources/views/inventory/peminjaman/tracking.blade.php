@extends('layouts.app')

@section('title', 'Tracking Pengajuan Peminjaman')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-geo-alt me-2"></i>Tracking Pengajuan Peminjaman</h3>
                <p class="text-subtitle text-muted mb-0">Pantau proses tahapan permohonan peminjaman Anda secara langsung dari verifikasi hingga serah terima barang.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tracking Pengajuan</li>
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
        @forelse($peminjamans as $p)
            @php $b = $p->status_badge; @endphp
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                        <div>
                            <span class="badge bg-light-primary text-primary font-monospace fs-6">{{ $p->kode_transaksi }}</span>
                            <span class="ms-2 fw-bold text-dark">{{ $p->barang->nama }} ({{ $p->jumlah }} Unit)</span>
                        </div>
                        <div>
                            <span class="badge bg-{{ $b['color'] }} fs-6">
                                <i class="bi {{ $b['icon'] }} me-1"></i>{{ $b['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="col-12 col-md-4 mb-3">
                                <h6 class="text-muted mb-2">Informasi Peminjaman</h6>
                                <p class="mb-1"><strong>Pemohon:</strong> {{ $p->user->name }} ({{ $p->user->department }})</p>
                                <p class="mb-1"><strong>Tanggal Pinjam:</strong> {{ $p->tgl_pinjam->format('d M Y') }}</p>
                                <p class="mb-1"><strong>Rencana Kembali:</strong> {{ $p->tgl_kembali_rencana ? $p->tgl_kembali_rencana->format('d M Y') : '-' }}</p>
                                <p class="mb-1"><strong>Keperluan:</strong> {{ $p->keperluan }}</p>
                                @if($p->catatan_manager)
                                    <div class="alert alert-light-warning small mt-2 mb-0 py-2">
                                        <strong>Catatan Manager:</strong> {{ $p->catatan_manager }}
                                    </div>
                                @endif
                                @if($p->catatan_staff)
                                    <div class="alert alert-light-info small mt-2 mb-0 py-2">
                                        <strong>Catatan Staff Accounting:</strong> {{ $p->catatan_staff }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-md-8">
                                <h6 class="text-muted mb-3">Progres Tahapan Tiket</h6>
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center position-relative gap-3 p-3 bg-light rounded-3">
                                    {{-- Tahap 1: Pengajuan --}}
                                    <div class="text-center d-flex flex-sm-column align-items-center gap-2">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-file-earmark-check"></i>
                                        </div>
                                        <div class="text-start text-sm-center">
                                            <strong class="d-block small">1. Diajukan</strong>
                                            <small class="text-muted">{{ $p->created_at->format('d/m/Y') }}</small>
                                        </div>
                                    </div>

                                    <div class="d-none d-sm-block flex-grow-1 border-top border-2 {{ in_array($p->status, ['disetujui', 'diserahkan', 'dikembalikan']) ? 'border-success' : ($p->status === 'ditolak' ? 'border-danger' : 'border-secondary') }}"></div>

                                    {{-- Tahap 2: Verifikasi Manager --}}
                                    <div class="text-center d-flex flex-sm-column align-items-center gap-2">
                                        <div class="rounded-circle {{ in_array($p->status, ['disetujui', 'diserahkan', 'dikembalikan']) ? 'bg-success text-white' : ($p->status === 'ditolak' ? 'bg-danger text-white' : 'bg-warning text-dark') }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi {{ $p->status === 'ditolak' ? 'bi-x' : 'bi-shield-check' }}"></i>
                                        </div>
                                        <div class="text-start text-sm-center">
                                            <strong class="d-block small">2. Verifikasi Manager</strong>
                                            <small class="text-muted">{{ $p->status === 'ditolak' ? 'Ditolak' : ($p->diverifikasi_oleh ? 'Disetujui' : 'Menunggu') }}</small>
                                        </div>
                                    </div>

                                    <div class="d-none d-sm-block flex-grow-1 border-top border-2 {{ in_array($p->status, ['diserahkan', 'dikembalikan']) ? 'border-success' : 'border-secondary' }}"></div>

                                    {{-- Tahap 3: Serah Terima Accounting --}}
                                    <div class="text-center d-flex flex-sm-column align-items-center gap-2">
                                        <div class="rounded-circle {{ in_array($p->status, ['diserahkan', 'dikembalikan']) ? 'bg-success text-white' : 'bg-secondary text-white' }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-arrow-right"></i>
                                        </div>
                                        <div class="text-start text-sm-center">
                                            <strong class="d-block small">3. Serah Terima</strong>
                                            <small class="text-muted">{{ $p->tgl_diserahkan ? $p->tgl_diserahkan->format('d/m/Y') : 'Belum' }}</small>
                                        </div>
                                    </div>

                                    <div class="d-none d-sm-block flex-grow-1 border-top border-2 {{ $p->status === 'dikembalikan' ? 'border-success' : 'border-secondary' }}"></div>

                                    {{-- Tahap 4: Pengembalian Selesai --}}
                                    <div class="text-center d-flex flex-sm-column align-items-center gap-2">
                                        <div class="rounded-circle {{ $p->status === 'dikembalikan' ? 'bg-success text-white' : 'bg-secondary text-white' }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-check2-all"></i>
                                        </div>
                                        <div class="text-start text-sm-center">
                                            <strong class="d-block small">4. Selesai</strong>
                                            <small class="text-muted">{{ $p->tgl_kembali_aktual ? $p->tgl_kembali_aktual->format('d/m/Y') : 'Belum' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                    <h5>Belum Ada Pengajuan Peminjaman</h5>
                    <p class="text-muted">Ajukan peminjaman alat atau mesin baru untuk melacak prosesnya di sini.</p>
                    <div>
                        <a href="{{ route('inventory.peminjaman.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Ajukan Peminjaman Sekarang
                        </a>
                    </div>
                </div>
            </div>
        @endforelse

        <div class="col-12">
            {{ $peminjamans->links() }}
        </div>
    </div>
</div>
@endsection
