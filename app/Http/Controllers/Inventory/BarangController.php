<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\TrackingBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->input('kondisi'));
        }

        $barangs = $query->latest()->paginate(10)->withQueryString();
        $kategories = Barang::select('kategori')->distinct()->pluck('kategori');

        return view('inventory.barang.index', compact('barangs', 'kategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:barangs,kode',
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'stok_total' => 'required|integer|min:1',
            'kondisi' => 'required|in:baik,maintenance,rusak_ringan,rusak_berat',
            'lokasi' => 'required|string',
            'golongan_fiskal' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['stok_tersedia'] = $validated['stok_total'];

        $barang = Barang::create($validated);

        TrackingBarang::create([
            'barang_id' => $barang->id,
            'lokasi_sekarang' => $barang->lokasi,
            'status_posisi' => 'Di Gudang',
            'keterangan' => 'Master barang baru didaftarkan ke inventaris.',
            'diperbarui_oleh' => Auth::id(),
        ]);

        return redirect()->route('inventory.barang.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'stok_total' => 'required|integer|min:1',
            'kondisi' => 'required|in:baik,maintenance,rusak_ringan,rusak_berat',
            'lokasi' => 'required|string',
            'golongan_fiskal' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        $barang->update($validated);

        return redirect()->route('inventory.barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Fitur khusus Staff Accounting: Pemeriksaan Ketersediaan & Kondisi Stok
     */
    public function ketersediaan(Request $request)
    {
        $barangs = Barang::withCount(['peminjamans' => function ($q) {
            $q->where('status', 'diserahkan');
        }])->latest()->get();

        return view('inventory.barang.ketersediaan', compact('barangs'));
    }

    public function updateKondisi(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->kondisi = $request->input('kondisi');
        $barang->lokasi = $request->input('lokasi', $barang->lokasi);
        $barang->save();

        TrackingBarang::create([
            'barang_id' => $barang->id,
            'lokasi_sekarang' => $barang->lokasi,
            'status_posisi' => $barang->kondisi === 'maintenance' ? 'Dalam Maintenance' : 'Pemeriksaan Stok',
            'keterangan' => 'Pemeriksaan ketersediaan oleh Staff Accounting. Catatan: ' . ($request->input('keterangan') ?? 'Status kondisi diubah menjadi ' . $barang->kondisi),
            'diperbarui_oleh' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Status ketersediaan barang {$barang->nama} berhasil diperbarui.");
    }
}
