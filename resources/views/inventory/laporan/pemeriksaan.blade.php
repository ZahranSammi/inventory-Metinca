@extends('layouts.app')

@section('title', 'Pemeriksaan & Arsip Laporan')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="mb-1 text-primary"><i class="bi bi-archive me-2"></i>Pemeriksaan & Arsip Laporan</h3>
                <p class="text-subtitle text-muted mb-0">Halaman eksekutif Direktur SDM untuk evaluasi, validasi, dan pengarsipan resmi laporan barang operasional perusahaan.</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pemeriksaan & Arsip Laporan</li>
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

    <div class="row mb-4">
        <div class="col-12 col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl bg-light-warning me-3">
                        <i class="bi bi-file-earmark-check fs-2 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Laporan Perlu Pemeriksaan</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalDiajukan }} <span class="fs-6 text-muted fw-normal">Dokumen Baru</span></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl bg-light-success me-3">
                        <i class="bi bi-archive-fill fs-2 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Arsip Resmi</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalArsip }} <span class="fs-6 text-muted fw-normal">Laporan Diarsipkan</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Dokumen Laporan Masuk</h5>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Laporan</th>
                            <th>Judul & Periode</th>
                            <th>Disusun Oleh</th>
                            <th>Status Dokumen</th>
                            <th>Arsip / Reviewer</th>
                            <th class="text-center" style="min-width: 170px;">Tindakan Direktur</th>
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
                                    <small class="text-muted">{{ $lap->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $b['color'] }}">{{ $b['label'] }}</span>
                                </td>
                                <td>
                                    @if($lap->status === 'diarsipkan')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Arsip Resmi</span>
                                        <small class="d-block text-muted">{{ $lap->tgl_arsip ? $lap->tgl_arsip->format('d/m/Y') : '' }}</small>
                                    @else
                                        <span class="text-muted small">- Belum diarsipkan -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalReview{{ $lap->id }}">
                                        <i class="bi bi-search me-1"></i> Periksa & Arsip
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Pemeriksaan & Aksi Arsip oleh Direktur SDM --}}
                            <div class="modal fade text-start" id="modalReview{{ $lap->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-shield-check text-primary me-2"></i>Pemeriksaan Dokumen ({{ $lap->nomor_laporan }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('inventory.laporan.review', $lap->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-light-primary">
                                                    <h6 class="mb-1 fw-bold">{{ $lap->judul }}</h6>
                                                    <small class="text-muted">Periode: {{ $lap->periode }} &bull; Penyusun: {{ $lap->penyusun->name }} ({{ $lap->penyusun->department }})</small>
                                                </div>

                                                <div class="card border bg-light p-3 mb-3">
                                                    <strong>Catatan / Ringkasan Evaluasi Manager:</strong>
                                                    <p class="mb-0 text-muted">{{ $lap->catatan_penyusun ?? 'Tidak ada catatan tambahan.' }}</p>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <div class="border rounded p-2 text-center bg-white">
                                                            <small class="text-muted d-block">Total Aset</small>
                                                            <strong class="fs-5">{{ $lap->ringkasan_stok['total_barang'] ?? ($lap->ringkasan_stok['total_aset'] ?? '-') }} Unit</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="border rounded p-2 text-center bg-white">
                                                            <small class="text-muted d-block">Kondisi Baik</small>
                                                            <strong class="fs-5 text-success">{{ $lap->ringkasan_stok['stok_baik'] ?? ($lap->ringkasan_stok['kondisi_baik'] ?? '-') }} Unit</strong>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Tindakan Pemeriksaan</label>
                                                    <select name="action" class="form-select" required>
                                                        <option value="arsip" {{ $lap->status === 'diarsipkan' ? 'selected' : '' }}>Setujui & Arsipkan Laporan Resmi</option>
                                                        <option value="setujui">Setujui (Tanpa Diarsipkan Dulu)</option>
                                                        <option value="revisi">Kembalikan untuk Direvisi Manager</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Catatan / Arahan Direktur SDM</label>
                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Tuliskan arahan tindak lanjut, persetujuan anggaran perawatan, atau catatan revisi..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success"><i class="bi bi-archive-fill me-1"></i> Simpan Hasil Pemeriksaan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada dokumen laporan operasional.</td>
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
@endsection
