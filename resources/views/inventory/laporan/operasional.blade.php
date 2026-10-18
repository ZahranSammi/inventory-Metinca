@extends('layouts.app')

@section('title', 'Laporan Barang Operasional')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="mb-1 text-primary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Barang Operasional</h3>
                <p class="text-subtitle text-muted mb-0">Penyusunan dan pengajuan laporan berkala penggunaan inventaris dan aset operasional oleh Manager ke Direktur SDM.</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Laporan Operasional</li>
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

    {{-- Ringkasan Metrik Cepat untuk Laporan --}}
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Total Aset Terdata</h6>
                    <h4 class="mb-0 fw-bold">{{ $rekapStok['total_barang'] }} Unit</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Aset Kondisi Baik</h6>
                    <h4 class="mb-0 fw-bold text-success">{{ $rekapStok['stok_baik'] }} Unit</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Sedang Maintenance</h6>
                    <h4 class="mb-0 fw-bold text-warning">{{ $rekapStok['stok_maintenance'] }} Unit</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Barang Sedang di Lapangan</h6>
                    <h4 class="mb-0 fw-bold text-primary">{{ $rekapPeminjaman['sedang_dipinjam'] }} Unit</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Daftar Laporan Operasional yang Telah Diajukan</h5>
                <small class="text-muted">Laporan yang disubmit akan diteruskan ke Direktur SDM untuk pemeriksaan & pengarsipan</small>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBuatLaporan">
                <i class="bi bi-plus-circle me-1"></i> Buat Laporan Baru
            </button>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Laporan</th>
                            <th>Judul & Periode</th>
                            <th>Disusun Oleh</th>
                            <th>Status Pemeriksaan</th>
                            <th>Catatan Direktur SDM</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporans as $lap)
                            @php $b = $lap->status_badge; @endphp
                            <tr>
                                <td><span class="badge bg-light-primary text-primary font-monospace">{{ $lap->nomor_laporan }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $lap->judul }}</div>
                                    <small class="badge bg-light-secondary text-dark">{{ $lap->periode }}</small>
                                </td>
                                <td>
                                    <div class="small fw-bold">{{ $lap->penyusun->name }}</div>
                                    <small class="text-muted">{{ $lap->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $b['color'] }}">{{ $b['label'] }}</span>
                                </td>
                                <td>
                                    <small class="text-muted fst-italic">{{ $lap->catatan_revisi ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDetailLaporan{{ $lap->id }}" title="Detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Detail Laporan --}}
                            <div class="modal fade" id="modalDetailLaporan{{ $lap->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Rincian Laporan ({{ $lap->nomor_laporan }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6>{{ $lap->judul }}</h6>
                                            <p class="text-muted small">Periode: {{ $lap->periode }} &bull; Disusun oleh: {{ $lap->penyusun->name }}</p>
                                            
                                            <div class="card bg-light p-3 mb-3 border-0">
                                                <strong>Catatan Manager:</strong>
                                                <p class="mb-0 text-muted">{{ $lap->catatan_penyusun ?? 'Tidak ada catatan tambahan.' }}</p>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="border rounded p-2">
                                                        <small class="text-muted d-block">Total Aset</small>
                                                        <strong>{{ $lap->ringkasan_stok['total_barang'] ?? ($lap->ringkasan_stok['total_aset'] ?? '-') }} Unit</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="border rounded p-2">
                                                        <small class="text-muted d-block">Kondisi Baik</small>
                                                        <strong class="text-success">{{ $lap->ringkasan_stok['stok_baik'] ?? ($lap->ringkasan_stok['kondisi_baik'] ?? '-') }} Unit</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-light-{{ $b['color'] }}">
                                                <strong>Status:</strong> {{ $b['label'] }}<br>
                                                @if($lap->reviewer)
                                                    <small class="text-muted">Direview oleh: {{ $lap->reviewer->name }} pada {{ $lap->tgl_review ? $lap->tgl_review->format('d M Y H:i') : '-' }}</small><br>
                                                @endif
                                                @if($lap->catatan_revisi)
                                                    <small><strong>Catatan Direktur:</strong> {{ $lap->catatan_revisi }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada laporan operasional yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $laporans->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Buat Laporan Baru --}}
<div class="modal fade" id="modalBuatLaporan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Susun Laporan Barang Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.laporan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label font-bold">Judul Laporan</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Laporan Rekapitulasi Inventaris Pabrik Bulan Oktober 2026" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Periode Laporan</label>
                            <input type="text" name="periode" class="form-control" placeholder="Contoh: Oktober 2026" value="{{ date('F Y') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-bold">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-bold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-bold">Ringkasan & Catatan Manager</label>
                            <textarea name="catatan_penyusun" class="form-control" rows="3" placeholder="Tuliskan evaluasi kondisi alat operasional, kendala pemakaian, atau rekomendasi pengadaan baru..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-1"></i> Ajukan ke Direktur SDM</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
