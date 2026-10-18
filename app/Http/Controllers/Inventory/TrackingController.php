<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\TrackingBarang;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = TrackingBarang::with(['barang', 'peminjaman.user', 'petugas']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('barang', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            })->orWhere('lokasi_sekarang', 'like', "%{$search}%")
              ->orWhere('status_posisi', 'like', "%{$search}%");
        }

        $trackings = $query->latest()->paginate(15)->withQueryString();

        // Barang yang sedang di luar (dipinjam)
        $barangDiLuar = Peminjaman::where('status', 'diserahkan')->with(['barang', 'user'])->get();

        return view('inventory.tracking.index', compact('trackings', 'barangDiLuar'));
    }
}
