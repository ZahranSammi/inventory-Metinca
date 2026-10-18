@extends('layouts.app')

@section('title', 'Pemeriksaan Ketersediaan Stok')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-clipboard-check me-2"></i>Pemeriksaan Ketersediaan</h3>
                <p class="text-subtitle text-muted mb-0">Inspeksi fisik ketersediaan stok barang operasional dan evaluasi kondisi alat.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pemeriksaan Ketersediaan</li>
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
                <h5 class="card-title mb-0">Status Ketersediaan Fisik di Gudang & Lapangan</h5>
                <small class="text-muted">Staff Accounting dapat memperbarui status kondisi saat pemeriksaan rutin</small>
            </div>
            <a href="{{ route('inventory.barang.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Lokasi Rak / Ruang</th>
                            <th class="text-center">Total Unit</th>
                            <th class="text-center">Tersedia di Gudang</th>
                            <th class="text-center">Sedang Dipinjam</th>
                            <th>Kondisi Fisik</th>
                            <th class="text-center">Aksi Pemeriksaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $b)
                            <tr>
                                <td class="font-monospace fw-bold text-primary">{{ $b->kode }}</td>
                                <td>
                                    <div class="fw-bold">{{ $b->nama }}</div>
                                    <small class="text-muted">{{ $b->kategori }}</small>
                                </td>
                                <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $b->lokasi }}</td>
                                <td class="text-center fw-bold">{{ $b->stok_total }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $b->stok_tersedia > 0 ? 'success' : 'danger' }} fs-7">
                                        {{ $b->stok_tersedia }} Unit
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-info text-info">
                                        {{ $b->peminjamans_count }} Dipinjam
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $b->kondisi_badge }} text-uppercase">
                                        {{ str_replace('_', ' ', $b->kondisi) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPeriksa{{ $b->id }}">
                                        <i class="bi bi-sliders me-1"></i> Update Kondisi
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Update Pemeriksaan Kondisi --}}
                            <div class="modal fade" id="modalPeriksa{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>Pemeriksaan Fisik Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('inventory.barang.ketersediaan.update', $b->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-light-primary mb-3">
                                                    <strong>{{ $b->nama }}</strong> ({{ $b->kode }})<br>
                                                    <small class="text-muted">Stok Total: {{ $b->stok_total }} &bull; Tersedia: {{ $b->stok_tersedia }} &bull; Lokasi: {{ $b->lokasi }}</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Kondisi Fisik Terkini</label>
                                                    <select name="kondisi" class="form-select" required>
                                                        <option value="baik" {{ $b->kondisi === 'baik' ? 'selected' : '' }}>Baik (Siap Pakai)</option>
                                                        <option value="maintenance" {{ $b->kondisi === 'maintenance' ? 'selected' : '' }}>Dalam Perawatan (Maintenance)</option>
                                                        <option value="rusak_ringan" {{ $b->kondisi === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                                        <option value="rusak_berat" {{ $b->kondisi === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat (Afkir)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Lokasi Penyimpanan / Rak</label>
                                                    <input type="text" name="lokasi" class="form-control" value="{{ $b->lokasi }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold">Catatan Hasil Pemeriksaan</label>
                                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Telah diuji fungsi hidrolik, siap digunakan."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Simpan Hasil Pemeriksaan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
