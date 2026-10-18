{{-- Include layout utama (Sidebar dan footer) --}}
@extends('layouts.app')

{{-- Set title berdasarkan page --}}
@section('title', 'Inventaris Perpajakan & Aset')

@push('styles')
<style>
    .card-stat {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px;
    }
    .card-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    .badge-fiskal {
        font-weight: 500;
        font-size: 0.8rem;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

{{-- Isi content --}}
@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3 class="mb-1 text-primary"><i class="bi bi-arrow-left-right me-2"></i>Inventaris Perpajakan & Aset</h3>
                <p class="text-subtitle text-muted mb-0">Manajemen inventaris, aktiva tetap, dan penyusutan fiskal terintegrasi ERP Metinca.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first text-md-end mb-3 mb-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home.main') }}"><i class="bi bi-house-door me-1"></i>Homepage</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inventaris</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <section class="section">
        <div class="row">
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card card-stat h-100 shadow-sm border-0">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon purple me-3">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1">Total Unit Inventaris</h6>
                                <h4 class="font-extrabold mb-0">1.248 <span class="fs-6 fw-normal text-muted">Item</span></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card card-stat h-100 shadow-sm border-0">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon blue me-3">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1">Nilai Perolehan Fiskal</h6>
                                <h4 class="font-extrabold mb-0">Rp 18,45 M</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card card-stat h-100 shadow-sm border-0">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon red me-3">
                                <i class="bi bi-graph-down-arrow"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1">Akumulasi Depresiasi</h6>
                                <h4 class="font-extrabold mb-0">Rp 5,21 M</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card card-stat h-100 shadow-sm border-0">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon green me-3">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1">PPN Masukan Terkredit</h6>
                                <h4 class="font-extrabold mb-0">Rp 1,62 M</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card & Data Table -->
        <div class="card shadow-sm border-0 mt-2">
            <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-0">Daftar Inventaris & Aset Manufaktur</h5>
                    <small class="text-muted">Sinkronisasi data master aset tetap dan amortisasi pajak (Tax Fixed Assets)</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Rekap
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="alert('Laporan inventaris pajak berhasil diekspor ke format Excel (.xlsx)')">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahInventaris">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Inventaris
                    </button>
                </div>
            </div>

            <div class="card-body pt-4">
                <!-- Search & Filters -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Cari nama barang, kode aset, atau faktur...">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" id="filterKategori">
                            <option value="">Semua Golongan Fiskal</option>
                            <option value="Golongan 1">Golongan 1 (Tarif 25% GL)</option>
                            <option value="Golongan 2">Golongan 2 (Tarif 12.5% GL)</option>
                            <option value="Golongan 3">Golongan 3 (Tarif 6.25% GL)</option>
                            <option value="Bangunan">Bangunan Permanen (Tarif 5% GL)</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif Beroperasi</option>
                            <option value="Maintenance">Dalam Perawatan</option>
                            <option value="Disusutkan Penuh">Disusutkan Penuh</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 text-md-end">
                        <button class="btn btn-light w-100" id="resetFilter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="inventoryTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Aset</th>
                                <th>Nama Barang / Mesin</th>
                                <th>Kategori</th>
                                <th>Golongan Fiskal</th>
                                <th>Tgl Perolehan</th>
                                <th>Nilai Perolehan</th>
                                <th>Nilai Buku Fiskal</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-MC-001</span></td>
                                <td>
                                    <div class="fw-bold">Mesin CNC Milling 5-Axis Makino</div>
                                    <small class="text-muted">FP: 010.000-23.89124401</small>
                                </td>
                                <td>Machining Division</td>
                                <td><span class="badge bg-info badge-fiskal">Golongan 2 (8 Thn)</span></td>
                                <td>15 Jan 2023</td>
                                <td class="fw-semibold">Rp 3.850.000.000</td>
                                <td class="text-success fw-bold">Rp 2.887.500.000</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-CST-014</span></td>
                                <td>
                                    <div class="fw-bold">Induction Melting Furnace 500kg</div>
                                    <small class="text-muted">FP: 010.000-22.11290384</small>
                                </td>
                                <td>Casting Foundry</td>
                                <td><span class="badge bg-info badge-fiskal">Golongan 2 (8 Thn)</span></td>
                                <td>10 Agt 2022</td>
                                <td class="fw-semibold">Rp 2.100.000.000</td>
                                <td class="text-success fw-bold">Rp 1.312.500.000</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-QA-008</span></td>
                                <td>
                                    <div class="fw-bold">CMM Optical Spectrometer Zeiss</div>
                                    <small class="text-muted">FP: 010.000-24.00412890</small>
                                </td>
                                <td>Quality Assurance</td>
                                <td><span class="badge bg-warning badge-fiskal">Golongan 1 (4 Thn)</span></td>
                                <td>02 Mar 2024</td>
                                <td class="fw-semibold">Rp 820.000.000</td>
                                <td class="text-success fw-bold">Rp 615.000.000</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-LOG-005</span></td>
                                <td>
                                    <div class="fw-bold">Forklift Elektrik Toyota 3.0 Ton</div>
                                    <small class="text-muted">FP: 010.000-21.78201982</small>
                                </td>
                                <td>Warehouse & PPIC</td>
                                <td><span class="badge bg-info badge-fiskal">Golongan 2 (8 Thn)</span></td>
                                <td>18 Nov 2021</td>
                                <td class="fw-semibold">Rp 450.000.000</td>
                                <td class="text-success fw-bold">Rp 225.000.000</td>
                                <td><span class="badge bg-warning">Maintenance</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-BLD-002</span></td>
                                <td>
                                    <div class="fw-bold">Gedung Workshop Foundry & Machining</div>
                                    <small class="text-muted">FP: 010.000-15.00918234</small>
                                </td>
                                <td>Infrastruktur Pabrik</td>
                                <td><span class="badge bg-secondary badge-fiskal">Bangunan (20 Thn)</span></td>
                                <td>05 Jan 2015</td>
                                <td class="fw-semibold">Rp 9.500.000.000</td>
                                <td class="text-success fw-bold">Rp 4.750.000.000</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td><span class="badge bg-light-primary text-primary font-monospace">AST-IT-019</span></td>
                                <td>
                                    <div class="fw-bold">Server ERP Database Dell PowerEdge</div>
                                    <small class="text-muted">FP: 010.000-20.44918201</small>
                                </td>
                                <td>IT & Sistem Informasi</td>
                                <td><span class="badge bg-warning badge-fiskal">Golongan 1 (4 Thn)</span></td>
                                <td>12 Feb 2020</td>
                                <td class="fw-semibold">Rp 180.000.000</td>
                                <td class="text-muted fw-bold">Rp 0 (Habis)</td>
                                <td><span class="badge bg-secondary">Disusutkan Penuh</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination & Information -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <small class="text-muted">Menampilkan 1 sampai 6 dari 6 entri inventaris (Sampel Data)</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item disabled"><a class="page-link" href="#">Selanjutnya</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Tambah Inventaris -->
<div class="modal fade" id="modalTambahInventaris" tabindex="-1" aria-labelledby="modalTambahInventarisLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahInventarisLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Inventaris / Aset Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahInventaris" onsubmit="event.preventDefault(); alert('Data inventaris berhasil disimpan!'); const modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahInventaris')); modal.hide();">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-bold">Kode Aset / Inventaris</label>
                            <input type="text" class="form-control" placeholder="Contoh: AST-MC-002" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Nama Barang / Mesin</label>
                            <input type="text" class="form-control" placeholder="Nama lengkap aset" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Kategori / Divisi</label>
                            <select class="form-select" required>
                                <option value="">Pilih Divisi...</option>
                                <option value="Machining">Machining Division</option>
                                <option value="Casting">Casting Foundry</option>
                                <option value="Warehouse">Warehouse & PPIC</option>
                                <option value="QA">Quality Assurance</option>
                                <option value="Finance">Finance & Taxation</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Golongan Harta Fiskal (Pajak)</label>
                            <select class="form-select" required>
                                <option value="">Pilih Golongan Fiskal...</option>
                                <option value="Golongan 1">Golongan 1 (Masa 4 Tahun - Tarif 25%)</option>
                                <option value="Golongan 2">Golongan 2 (Masa 8 Tahun - Tarif 12.5%)</option>
                                <option value="Golongan 3">Golongan 3 (Masa 16 Tahun - Tarif 6.25%)</option>
                                <option value="Bangunan">Bangunan Permanen (Masa 20 Tahun - Tarif 5%)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Tanggal Perolehan</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Nomor Faktur Pajak (PPN)</label>
                            <input type="text" class="form-control" placeholder="Contoh: 010.000-24.12345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Nilai Perolehan (Rp)</label>
                            <input type="number" class="form-control" placeholder="Rp 0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold">Metode Penyusutan Pajak</label>
                            <select class="form-select">
                                <option value="Garis Lurus">Garis Lurus (Straight Line)</option>
                                <option value="Saldo Menurun">Saldo Menurun (Declining Balance)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Inventaris</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Search Table
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // Filter Kategori
    document.getElementById('filterKategori').addEventListener('change', function() {
        const selected = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        rows.forEach(row => {
            const cellText = row.cells[4].innerText.toLowerCase();
            row.style.display = (selected === '' || cellText.includes(selected)) ? '' : 'none';
        });
    });

    // Filter Status
    document.getElementById('filterStatus').addEventListener('change', function() {
        const selected = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        rows.forEach(row => {
            const cellText = row.cells[8].innerText.toLowerCase();
            row.style.display = (selected === '' || cellText.includes(selected)) ? '' : 'none';
        });
    });

    // Reset Filter
    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterKategori').value = '';
        document.getElementById('filterStatus').value = '';
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        rows.forEach(row => row.style.display = '');
    });
</script>
@endpush
