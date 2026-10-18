<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Laporan;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * [Manager] Halaman Laporan Barang Operasional
     */
    public function operasional()
    {
        $laporans = Laporan::with(['penyusun', 'reviewer'])->latest()->paginate(10);

        // Rekap realtime untuk bahan penyusunan laporan
        $rekapStok = [
            'total_barang' => Barang::count(),
            'stok_baik' => Barang::where('kondisi', 'baik')->count(),
            'stok_maintenance' => Barang::where('kondisi', 'maintenance')->count(),
            'stok_rusak' => Barang::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count(),
        ];

        $rekapPeminjaman = [
            'total' => Peminjaman::count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'diserahkan')->count(),
            'selesai' => Peminjaman::where('status', 'dikembalikan')->count(),
            'ditolak' => Peminjaman::where('status', 'ditolak')->count(),
        ];

        return view('inventory.laporan.operasional', compact('laporans', 'rekapStok', 'rekapPeminjaman'));
    }

    /**
     * [Manager] Kirim Laporan Barang Operasional ke Direktur SDM
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'periode' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'catatan_penyusun' => 'nullable|string',
        ]);

        $nomorLaporan = 'LAP-INV-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -3));

        $rekapStok = [
            'total_barang' => Barang::count(),
            'stok_baik' => Barang::where('kondisi', 'baik')->count(),
            'stok_maintenance' => Barang::where('kondisi', 'maintenance')->count(),
        ];

        $rekapPeminjaman = [
            'total' => Peminjaman::count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'diserahkan')->count(),
            'selesai' => Peminjaman::where('status', 'dikembalikan')->count(),
        ];

        Laporan::create([
            'nomor_laporan' => $nomorLaporan,
            'judul' => $request->judul,
            'periode' => $request->periode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'status' => 'diajukan',
            'catatan_penyusun' => $request->catatan_penyusun,
            'ringkasan_stok' => $rekapStok,
            'ringkasan_peminjaman' => $rekapPeminjaman,
            'disusun_oleh' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Laporan operasional {$nomorLaporan} berhasil diajukan ke Direktur SDM.");
    }

    /**
     * [Direktur SDM] Pemeriksaan Laporan & Arsipkan Laporan
     */
    public function pemeriksaan()
    {
        $laporans = Laporan::with(['penyusun', 'reviewer'])->latest()->paginate(10);
        $totalDiajukan = Laporan::where('status', 'diajukan')->count();
        $totalArsip = Laporan::where('status', 'diarsipkan')->count();

        return view('inventory.laporan.pemeriksaan', compact('laporans', 'totalDiajukan', 'totalArsip'));
    }

    /**
     * [Direktur SDM] Aksi Persetujuan / Arsipkan Laporan
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:arsip,setujui,revisi',
            'catatan' => 'nullable|string',
        ]);

        $laporan = Laporan::findOrFail($id);
        $laporan->direview_oleh = Auth::id();
        $laporan->tgl_review = now();

        if ($request->action === 'arsip') {
            $laporan->status = 'diarsipkan';
            $laporan->tgl_arsip = now();
            $laporan->catatan_revisi = $request->catatan;
            $msg = "Laporan {$laporan->nomor_laporan} berhasil disetujui dan resmi diarsipkan.";
        } elseif ($request->action === 'setujui') {
            $laporan->status = 'disetujui';
            $laporan->catatan_revisi = $request->catatan;
            $msg = "Laporan {$laporan->nomor_laporan} telah disetujui Direktur SDM.";
        } else {
            $laporan->status = 'direvisi';
            $laporan->catatan_revisi = $request->catatan ?? 'Perlu perbaikan data operasional.';
            $msg = "Laporan {$laporan->nomor_laporan} dikembalikan untuk direvisi Manager.";
        }

        $laporan->save();

        return redirect()->back()->with('success', $msg);
    }
}
