@extends('layouts.app')

@section('title', 'Data Barang Inventaris')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1 text-primary"><i class="bi bi-box-seam me-2"></i>Data Barang Operasional</h3>
                <p class="text-subtitle text-muted mb-0">Katalog master barang, mesin, perkakas, dan peralatan operasional PT. Metinca.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Barang</li>
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
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-0">Daftar Barang & Fasilitas</h5>
                <small class="text-muted">Total terdaftar: {{ $barangs->total() }} barang</small>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->check() && auth()->user()->isAccounting())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Barang Baru
                    </button>
                    <a href="{{ route('inventory.barang.ketersediaan') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-clipboard-check me-1"></i> Pemeriksaan Ketersediaan
                    </a>
                @endif
                @if(auth()->check() && auth()->user()->isKaryawan())
                    <a href="{{ route('inventory.peminjaman.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i> Ajukan Peminjaman
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body pt-3">
            {{-- Filter Bar --}}
            <form action="{{ route('inventory.barang.index') }}" method="GET" class="row g-2 mb-4">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama barang, kode, atau lokasi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="kategori" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($kategories as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="kondisi" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kondisi</option>
                        <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="maintenance" {{ request('kondisi') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="rusak_ringan" {{ request('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('inventory.barang.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi Fisik</th>
                            <th>Stok Total</th>
                            <th>Tersedia</th>
                            <th>Kondisi</th>
                            @if(auth()->check() && (auth()->user()->isAccounting() || auth()->user()->isKaryawan()))
                                <th class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangs as $index => $b)
                            <tr>
                                <td>{{ $barangs->firstItem() + $index }}</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">{{ $b->kode }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $b->nama }}</div>
                                    <small class="text-muted">{{ $b->deskripsi ?? 'Tanpa deskripsi' }}</small>
                                </td>
                                <td><span class="badge bg-light-secondary text-dark">{{ $b->kategori }}</span></td>
                                <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $b->lokasi }}</td>
                                <td class="fw-bold">{{ $b->stok_total }}</td>
                                <td>
                                    <span class="badge bg-{{ $b->stok_tersedia > 0 ? 'success' : 'danger' }} fs-7">
                                        {{ $b->stok_tersedia }} Unit
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $b->kondisi_badge }} text-uppercase">
                                        {{ str_replace('_', ' ', $b->kondisi) }}
                                    </span>
                                </td>
                                @if(auth()->check() && auth()->user()->isAccounting())
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditBarang{{ $b->id }}" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                @elseif(auth()->check() && auth()->user()->isKaryawan())
                                    <td class="text-center">
                                        @if($b->stok_tersedia > 0 && $b->kondisi === 'baik')
                                            <a href="{{ route('inventory.peminjaman.create') }}?barang_id={{ $b->id }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-hand-index-thumb me-1"></i> Pinjam
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">Tidak Tersedia</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            {{-- Modal Edit (Hanya untuk Staff Accounting) --}}
                            @if(auth()->check() && auth()->user()->isAccounting())
                                <div class="modal fade" id="modalEditBarang{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Data Barang ({{ $b->kode }})</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('inventory.barang.update', $b->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label font-bold">Nama Barang</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $b->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-bold">Kategori</label>
                                                        <input type="text" name="kategori" class="form-control" value="{{ $b->kategori }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label font-bold">Stok Total</label>
                                                            <input type="number" name="stok_total" class="form-control" value="{{ $b->stok_total }}" min="1" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label font-bold">Kondisi</label>
                                                            <select name="kondisi" class="form-select" required>
                                                                <option value="baik" {{ $b->kondisi == 'baik' ? 'selected' : '' }}>Baik</option>
                                                                <option value="maintenance" {{ $b->kondisi == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                                <option value="rusak_ringan" {{ $b->kondisi == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                                                <option value="rusak_berat" {{ $b->kondisi == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-bold">Lokasi Fisik / Ruangan</label>
                                                        <input type="text" name="lokasi" class="form-control" value="{{ $b->lokasi }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-bold">Deskripsi</label>
                                                        <textarea name="deskripsi" class="form-control" rows="2">{{ $b->deskripsi }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data barang yang sesuai kriteria pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $barangs->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Barang Baru (Staff Accounting) --}}
@if(auth()->check() && auth()->user()->isAccounting())
    <div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Barang Baru ke Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('inventory.barang.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-bold">Kode Barang</label>
                                <input type="text" name="kode" class="form-control" placeholder="Contoh: AST-MC-003" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Nama Barang</label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama alat/mesin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">Pilih Kategori...</option>
                                    <option value="Machining">Machining</option>
                                    <option value="Casting">Casting</option>
                                    <option value="Quality Assurance">Quality Assurance</option>
                                    <option value="Warehouse & PPIC">Warehouse & PPIC</option>
                                    <option value="Perkakas Lapangan">Perkakas Lapangan</option>
                                    <option value="IT & Operasional">IT & Operasional</option>
                                    <option value="Kendaraan Operasional">Kendaraan Operasional</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">Golongan Fiskal (Pajak)</label>
                                <select name="golongan_fiskal" class="form-select">
                                    <option value="Golongan 1 (4 Thn)">Golongan 1 (4 Tahun)</option>
                                    <option value="Golongan 2 (8 Thn)">Golongan 2 (8 Tahun)</option>
                                    <option value="Golongan 3 (16 Thn)">Golongan 3 (16 Tahun)</option>
                                    <option value="Bangunan (20 Thn)">Bangunan Permanen (20 Tahun)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">Stok Total</label>
                                <input type="number" name="stok_total" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">Kondisi Awal</label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="baik">Baik (Siap Pakai)</option>
                                    <option value="maintenance">Dalam Perawatan</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">Lokasi Ruangan / Rak</label>
                                <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Workshop Bay B" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-bold">Deskripsi / Spesifikasi Singkat</label>
                                <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan teknis barang"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
