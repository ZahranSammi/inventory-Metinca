@extends('layouts.app')

@section('title', 'Peminjaman Barang & Informasi')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="mb-1 text-primary"><i class="bi bi-receipt me-2"></i>Peminjaman Barang & Informasi</h3>
                <p class="text-subtitle text-muted mb-0">Pengelolaan tiket peminjaman, serah terima fisik barang, dan pencatatan pengembalian inventaris.</p>
            </div>
            <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventaris</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Peminjaman Barang</li>
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-0">Daftar Tiket Peminjaman Operasional</h5>
                <small class="text-muted">Total: {{ $peminjamans->total() }} tiket transaksi</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventory.peminjaman.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Semua</a>
                <a href="{{ route('inventory.peminjaman.index') }}?status=disetujui" class="btn btn-sm {{ request('status') == 'disetujui' ? 'btn-info text-white' : 'btn-outline-info' }}">Siap Serah Terima</a>
                <a href="{{ route('inventory.peminjaman.index') }}?status=diserahkan" class="btn btn-sm {{ request('status') == 'diserahkan' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">Sedang Dipinjam</a>
                <a href="{{ route('inventory.peminjaman.index') }}?status=dikembalikan" class="btn btn-sm {{ request('status') == 'dikembalikan' ? 'btn-success' : 'btn-outline-success' }}">Selesai</a>
            </div>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Pemohon</th>
                            <th>Barang & Jumlah</th>
                            <th>Periode Pinjam</th>
                            <th>Status Tiket</th>
                            <th>Verifikator (Manager)</th>
                            <th class="text-center">Aksi Staff Accounting</th>
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
                                    <small class="badge bg-light-secondary text-dark">{{ $p->jumlah }} Unit</small>
                                    <small class="d-block text-muted text-truncate" style="max-width: 180px;">{{ $p->keperluan }}</small>
                                </td>
                                <td>
                                    <small class="d-block"><strong>Mulai:</strong> {{ $p->tgl_pinjam->format('d M Y') }}</small>
                                    <small class="d-block"><strong>Kembali:</strong> {{ $p->tgl_kembali_rencana ? $p->tgl_kembali_rencana->format('d M Y') : '-' }}</small>
                                </td>
                                <td>
                                    @php $b = $p->status_badge; @endphp
                                    <span class="badge bg-{{ $b['color'] }}">
                                        <i class="bi {{ $b['icon'] }} me-1"></i>{{ $b['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($p->verifikator)
                                        <div class="small fw-semibold">{{ $p->verifikator->name }}</div>
                                        <small class="text-muted fst-italic">"{{ $p->catatan_manager ?? 'Disetujui' }}"</small>
                                    @else
                                        <span class="text-muted small">- Belum diverifikasi -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->status === 'disetujui')
                                        {{-- Modal Serah Terima Fisik Barang --}}
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalSerah{{ $p->id }}">
                                            <i class="bi bi-box-arrow-right me-1"></i> Serahkan Barang
                                        </button>

                                        <div class="modal fade text-start" id="modalSerah{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i>Serah Terima Fisik & Informasi Peminjaman</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('inventory.peminjaman.serahkan', $p->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-light-info mb-3">
                                                                <strong>Tiket: {{ $p->kode_transaksi }}</strong><br>
                                                                Pemohon: {{ $p->user->name }} ({{ $p->user->department }})<br>
                                                                Barang: <strong>{{ $p->barang->nama }}</strong> (Jumlah: {{ $p->jumlah }} Unit)
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold">Lokasi Operasional Pemakaian</label>
                                                                <input type="text" name="lokasi_operasional" class="form-control" value="Workshop Machining Area Station 3" required>
                                                                <small class="text-muted">Tulis lokasi barang akan digunakan selama peminjaman.</small>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold">Catatan / Informasi Peminjaman</label>
                                                                <textarea name="catatan_staff" class="form-control" rows="2" placeholder="Catatan kondisi fisik saat serah terima, kelengkapan aksesoris, kabel, dll."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Konfirmasi Penyerahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($p->status === 'diserahkan')
                                        {{-- Modal Konfirmasi Pengembalian --}}
                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalKembali{{ $p->id }}">
                                            <i class="bi bi-arrow-return-left me-1"></i> Konfirmasi Kembali
                                        </button>

                                        <div class="modal fade text-start" id="modalKembali{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Konfirmasi Pengembalian Barang</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('inventory.peminjaman.kembalikan', $p->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-light-success mb-3">
                                                                Barang: <strong>{{ $p->barang->nama }}</strong><br>
                                                                Dipinjam oleh: {{ $p->user->name }} sejak {{ $p->tgl_pinjam->format('d M Y') }}
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold">Kondisi Barang Saat Dikembalikan</label>
                                                                <select name="kondisi_kembali" class="form-select" required>
                                                                    <option value="baik">Baik & Normal</option>
                                                                    <option value="maintenance">Perlu Maintenance / Servis Ringan</option>
                                                                    <option value="rusak_ringan">Ada Kerusakan Ringan</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold">Lokasi Rak Pengembalian di Gudang</label>
                                                                <input type="text" name="lokasi_rak" class="form-control" value="{{ $p->barang->lokasi }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2-all me-1"></i> Simpan Pengembalian</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($p->status === 'dikembalikan')
                                        <span class="badge bg-light-success text-success"><i class="bi bi-check2-all me-1"></i>Telah Dikembalikan</span>
                                    @elseif($p->status === 'ditolak')
                                        <span class="badge bg-light-danger text-danger">Tiket Ditolak</span>
                                    @else
                                        <span class="badge bg-light-warning text-warning">Menunggu Manager</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data peminjaman yang sesuai filter.</td>
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
