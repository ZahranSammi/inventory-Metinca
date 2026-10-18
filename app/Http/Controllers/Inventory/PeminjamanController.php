<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\TrackingBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminjamanController extends Controller
{
    /**
     * Tampilan umum daftar peminjaman / tiket aktif sesuai role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Peminjaman::with(['user', 'barang', 'verifikator', 'staffAccounting']);

        if ($user->isKaryawan()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('inventory.peminjaman.index', compact('peminjamans'));
    }

    /**
     * [Karyawan] Halaman formulir pengajuan peminjaman
     */
    public function create()
    {
        $barangs = Barang::where('stok_tersedia', '>', 0)
            ->where('kondisi', 'baik')
            ->orderBy('nama')
            ->get();

        return view('inventory.peminjaman.create', compact('barangs'));
    }

    /**
     * [Karyawan] Simpan pengajuan peminjaman
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date|after_or_equal:today',
            'tgl_kembali_rencana' => 'required|date|after_or_equal:tgl_pinjam',
            'keperluan' => 'required|string|max:1000',
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        if ($barang->stok_tersedia < $request->jumlah) {
            return redirect()->back()->withInput()->with('error', "Stok tersedia untuk {$barang->nama} tidak mencukupi (sisa: {$barang->stok_tersedia}).");
        }

        $kodeTransaksi = 'PMJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $peminjaman = Peminjaman::create([
            'kode_transaksi' => $kodeTransaksi,
            'user_id' => Auth::id(),
            'barang_id' => $barang->id,
            'jumlah' => $request->jumlah,
            'tgl_pinjam' => $request->tgl_pinjam,
            'tgl_kembali_rencana' => $request->tgl_kembali_rencana,
            'keperluan' => $request->keperluan,
            'status' => 'diajukan',
        ]);

        TrackingBarang::create([
            'barang_id' => $barang->id,
            'peminjaman_id' => $peminjaman->id,
            'lokasi_sekarang' => $barang->lokasi,
            'status_posisi' => 'Diajukan Peminjaman',
            'keterangan' => 'Pengajuan baru oleh ' . Auth::user()->name . " (Keperluan: {$request->keperluan})",
            'diperbarui_oleh' => Auth::id(),
        ]);

        return redirect()->route('inventory.peminjaman.tracking')->with('success', 'Pengajuan peminjaman berhasil dikirim. Menunggu verifikasi Manager.');
    }

    /**
     * [Karyawan] Tracking pengajuan peminjaman milik sendiri
     */
    public function tracking(Request $request)
    {
        $user = Auth::user();
        $query = Peminjaman::with(['barang', 'verifikator', 'staffAccounting', 'trackings']);

        if ($user->isKaryawan()) {
            $query->where('user_id', $user->id);
        }

        $peminjamans = $query->latest()->paginate(10);

        return view('inventory.peminjaman.tracking', compact('peminjamans'));
    }

    /**
     * [Karyawan] Riwayat peminjaman terdahulu (Selesai & Ditolak)
     */
    public function riwayat()
    {
        $user = Auth::user();
        $query = Peminjaman::with(['barang', 'verifikator', 'staffAccounting'])
            ->whereIn('status', ['dikembalikan', 'ditolak']);

        if ($user->isKaryawan()) {
            $query->where('user_id', $user->id);
        }

        $riwayats = $query->latest()->paginate(10);

        return view('inventory.peminjaman.riwayat', compact('riwayats'));
    }

    /**
     * [Manager] Verifikasi peminjaman barang (Daftar Menunggu)
     */
    public function verifikasiList()
    {
        $peminjamans = Peminjaman::with(['user', 'barang'])
            ->where('status', 'diajukan')
            ->latest()
            ->paginate(10);

        return view('inventory.peminjaman.verifikasi', compact('peminjamans'));
    }

    /**
     * [Manager] Aksi Setujui / Tolak Verifikasi Peminjaman
     */
    public function verifikasiAction(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan' => 'nullable|string',
        ]);

        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        if ($request->action === 'approve') {
            $peminjaman->status = 'disetujui';
            $peminjaman->diverifikasi_oleh = Auth::id();
            $peminjaman->catatan_manager = $request->catatan ?? 'Disetujui oleh Manager.';
            $peminjaman->save();

            TrackingBarang::create([
                'barang_id' => $peminjaman->barang_id,
                'peminjaman_id' => $peminjaman->id,
                'lokasi_sekarang' => $peminjaman->barang->lokasi,
                'status_posisi' => 'Disetujui Manager',
                'keterangan' => 'Peminjaman disetujui Manager: ' . $peminjaman->catatan_manager . '. Menunggu serah terima barang oleh Staff Accounting.',
                'diperbarui_oleh' => Auth::id(),
            ]);

            return redirect()->back()->with('success', "Pengajuan {$peminjaman->kode_transaksi} berhasil disetujui. Staff Accounting dapat menyerahkan barang.");
        } else {
            $peminjaman->status = 'ditolak';
            $peminjaman->diverifikasi_oleh = Auth::id();
            $peminjaman->catatan_manager = $request->catatan ?? 'Ditolak oleh Manager.';
            $peminjaman->save();

            TrackingBarang::create([
                'barang_id' => $peminjaman->barang_id,
                'peminjaman_id' => $peminjaman->id,
                'lokasi_sekarang' => $peminjaman->barang->lokasi,
                'status_posisi' => 'Ditolak Manager',
                'keterangan' => 'Pengajuan ditolak. Alasan: ' . $peminjaman->catatan_manager,
                'diperbarui_oleh' => Auth::id(),
            ]);

            return redirect()->back()->with('success', "Pengajuan {$peminjaman->kode_transaksi} telah ditolak.");
        }
    }

    /**
     * [Staff Accounting] Serah Terima Barang & Kirim Informasi Peminjaman
     */
    public function serahkan(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        if ($peminjaman->barang->stok_tersedia < $peminjaman->jumlah) {
            return redirect()->back()->with('error', 'Stok barang tidak mencukupi untuk diserahkan.');
        }

        // Kurangi stok tersedia
        $peminjaman->barang->decrement('stok_tersedia', $peminjaman->jumlah);

        $lokasiOperasional = $request->input('lokasi_operasional', 'Area Lapangan / Workshop Pabrik');

        $peminjaman->status = 'diserahkan';
        $peminjaman->tgl_diserahkan = now();
        $peminjaman->diproses_oleh = Auth::id();
        $peminjaman->catatan_staff = $request->input('catatan_staff', 'Barang telah diperiksa ketersediaannya dan diserahkan kepada pemohon.');
        $peminjaman->save();

        TrackingBarang::create([
            'barang_id' => $peminjaman->barang_id,
            'peminjaman_id' => $peminjaman->id,
            'lokasi_sekarang' => $lokasiOperasional,
            'status_posisi' => 'Sedang Digunakan Lapangan',
            'keterangan' => "Diserahkan oleh Staff Accounting ({Auth::user()->name}) kepada {$peminjaman->user->name}. Catatan: {$peminjaman->catatan_staff}",
            'diperbarui_oleh' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Barang {$peminjaman->barang->nama} berhasil diserahkan kepada {$peminjaman->user->name}. Informasi peminjaman telah tercatat.");
    }

    /**
     * [Staff Accounting] Konfirmasi Pengembalian Barang
     */
    public function kembalikan(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        // Kembalikan stok tersedia
        $peminjaman->barang->increment('stok_tersedia', $peminjaman->jumlah);

        $kondisiKembali = $request->input('kondisi_kembali', 'baik');
        $lokasiRak = $request->input('lokasi_rak', $peminjaman->barang->lokasi);

        if ($kondisiKembali !== 'baik') {
            $peminjaman->barang->kondisi = $kondisiKembali;
            $peminjaman->barang->save();
        }

        $peminjaman->status = 'dikembalikan';
        $peminjaman->tgl_kembali_aktual = now();
        $peminjaman->kondisi_kembali = $kondisiKembali;
        $peminjaman->save();

        TrackingBarang::create([
            'barang_id' => $peminjaman->barang_id,
            'peminjaman_id' => $peminjaman->id,
            'lokasi_sekarang' => $lokasiRak,
            'status_posisi' => 'Kembali ke Gudang',
            'keterangan' => "Barang telah dikembalikan dalam kondisi {$kondisiKembali}. Diterima oleh Staff Accounting ({Auth::user()->name}).",
            'diperbarui_oleh' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Barang {$peminjaman->barang->nama} berhasil dicatat kembali ke inventaris.");
    }
}
