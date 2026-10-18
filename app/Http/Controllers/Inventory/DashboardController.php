<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Laporan;
use App\Models\Peminjaman;
use App\Models\TrackingBarang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Auto-login default demo user (Karyawan) if not logged in
        if (!Auth::check()) {
            $defaultUser = User::where('role', 'karyawan')->first();
            if ($defaultUser) {
                Auth::login($defaultUser);
            }
        }

        $user = Auth::user();
        $role = $user?->role ?? 'karyawan';

        // General Stats
        $totalBarang = Barang::count();
        $barangTersedia = Barang::where('stok_tersedia', '>', 0)->count();
        $barangMaintenance = Barang::where('kondisi', 'maintenance')->count();

        // Role-Specific Data
        $data = [
            'user' => $user,
            'role' => $role,
            'totalBarang' => $totalBarang,
            'barangTersedia' => $barangTersedia,
            'barangMaintenance' => $barangMaintenance,
        ];

        if ($user && $user->isKaryawan()) {
            $data['peminjamanAktif'] = Peminjaman::where('user_id', $user->id)
                ->whereIn('status', ['diajukan', 'disetujui', 'diserahkan'])
                ->with('barang')
                ->latest()
                ->get();
            $data['totalDiajukan'] = Peminjaman::where('user_id', $user->id)->where('status', 'diajukan')->count();
            $data['totalDipinjam'] = Peminjaman::where('user_id', $user->id)->where('status', 'diserahkan')->count();
            $data['totalSelesai'] = Peminjaman::where('user_id', $user->id)->where('status', 'dikembalikan')->count();
        } elseif ($user && $user->isAccounting()) {
            $data['menungguPenyerahan'] = Peminjaman::where('status', 'disetujui')
                ->with(['user', 'barang'])
                ->latest()
                ->get();
            $data['sedangDipinjam'] = Peminjaman::where('status', 'diserahkan')
                ->with(['user', 'barang'])
                ->latest()
                ->get();
            $data['totalMenungguPenyerahan'] = Peminjaman::where('status', 'disetujui')->count();
            $data['totalSedangDipinjam'] = Peminjaman::where('status', 'diserahkan')->count();
        } elseif ($user && $user->isManager()) {
            $data['menungguVerifikasi'] = Peminjaman::where('status', 'diajukan')
                ->with(['user', 'barang'])
                ->latest()
                ->get();
            $data['totalMenungguVerifikasi'] = Peminjaman::where('status', 'diajukan')->count();
            $data['totalDisetujuiBulanIni'] = Peminjaman::whereIn('status', ['disetujui', 'diserahkan', 'dikembalikan'])
                ->whereMonth('created_at', now()->month)
                ->count();
            $data['laporanTerbaru'] = Laporan::latest()->first();
        } elseif ($user && $user->isDirektur()) {
            $data['laporanMenungguReview'] = Laporan::where('status', 'diajukan')->with('penyusun')->get();
            $data['totalLaporanMenunggu'] = Laporan::where('status', 'diajukan')->count();
            $data['totalLaporanDiarsipkan'] = Laporan::where('status', 'diarsipkan')->count();
            $data['recentPeminjamans'] = Peminjaman::with(['user', 'barang'])->latest()->take(5)->get();
        }

        $data['recentTrackings'] = TrackingBarang::with(['barang', 'peminjaman.user'])
            ->latest()
            ->take(6)
            ->get();

        return view('inventory.dashboard', $data);
    }
}
