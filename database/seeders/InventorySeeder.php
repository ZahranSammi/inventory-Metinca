<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Laporan;
use App\Models\Peminjaman;
use App\Models\TrackingBarang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo Users for 4 Roles
        $karyawan = User::updateOrCreate(
            ['email' => 'karyawan@metinca.co.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'department' => 'Machining & Assembly',
                'employee_id' => 'EMP-MC-042',
                'phone' => '0812-3456-7890',
            ]
        );

        $accounting = User::updateOrCreate(
            ['email' => 'accounting@metinca.co.id'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'staff_accounting',
                'department' => 'Accounting & Finance',
                'employee_id' => 'EMP-ACC-015',
                'phone' => '0813-9876-5432',
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@metinca.co.id'],
            [
                'name' => 'Ir. Hendro Wijaya, M.T.',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department' => 'Production & Engineering',
                'employee_id' => 'MGR-ENG-003',
                'phone' => '0811-2233-4455',
            ]
        );

        $direktur = User::updateOrCreate(
            ['email' => 'direktur@metinca.co.id'],
            [
                'name' => 'Dr. Bambang Sutrisno, M.M.',
                'password' => Hash::make('password'),
                'role' => 'direktur_sdm',
                'department' => 'Direksi & Human Capital',
                'employee_id' => 'DIR-HC-001',
                'phone' => '0819-8765-4321',
            ]
        );

        // 2. Create Master Barang
        $barangs = [
            [
                'kode' => 'AST-MC-001',
                'nama' => 'Mesin CNC Milling 5-Axis Makino',
                'kategori' => 'Machining',
                'stok_total' => 2,
                'stok_tersedia' => 1,
                'kondisi' => 'baik',
                'lokasi' => 'Workshop Machining Bay A',
                'golongan_fiskal' => 'Golongan 2 (8 Thn)',
                'deskripsi' => 'Presisi milling tinggi untuk pembuatan mold dan dies industri.',
            ],
            [
                'kode' => 'AST-CST-014',
                'nama' => 'Induction Melting Furnace 500kg',
                'kategori' => 'Casting',
                'stok_total' => 3,
                'stok_tersedia' => 3,
                'kondisi' => 'baik',
                'lokasi' => 'Casting Foundry Area 2',
                'golongan_fiskal' => 'Golongan 2 (8 Thn)',
                'deskripsi' => 'Tungku induksi peleburan logam besi & baja dengan suhu s/d 1650°C.',
            ],
            [
                'kode' => 'AST-QA-008',
                'nama' => 'CMM Optical Spectrometer Zeiss',
                'kategori' => 'Quality Assurance',
                'stok_total' => 1,
                'stok_tersedia' => 0,
                'kondisi' => 'baik',
                'lokasi' => 'Laboratorium QA & Metrologi',
                'golongan_fiskal' => 'Golongan 1 (4 Thn)',
                'deskripsi' => 'Alat ukur koordinat dimensional berakurasi sub-mikron.',
            ],
            [
                'kode' => 'AST-LOG-005',
                'nama' => 'Forklift Elektrik Toyota 3.0 Ton',
                'kategori' => 'Warehouse & PPIC',
                'stok_total' => 4,
                'stok_tersedia' => 3,
                'kondisi' => 'maintenance',
                'lokasi' => 'Gudang Material & Logistik',
                'golongan_fiskal' => 'Golongan 2 (8 Thn)',
                'deskripsi' => 'Armada angkut palet bahan baku dan cetakan berat.',
            ],
            [
                'kode' => 'AST-TL-021',
                'nama' => 'Hydraulic Torque Wrench Set Norbar',
                'kategori' => 'Perkakas Lapangan',
                'stok_total' => 5,
                'stok_tersedia' => 4,
                'kondisi' => 'baik',
                'lokasi' => 'Tool Crib Ruang Alat Lt. 1',
                'golongan_fiskal' => 'Golongan 1 (4 Thn)',
                'deskripsi' => 'Kunci torsi hidrolik untuk pengencangan baut flange pipa tekanan tinggi.',
            ],
            [
                'kode' => 'AST-IT-019',
                'nama' => 'Industrial Rugged Laptop Getac B360',
                'kategori' => 'IT & Operasional',
                'stok_total' => 6,
                'stok_tersedia' => 5,
                'kondisi' => 'baik',
                'lokasi' => 'Ruang Server & IT Support',
                'golongan_fiskal' => 'Golongan 1 (4 Thn)',
                'deskripsi' => 'Laptop industri anti-debu & getaran untuk teknisi kalibrasi mesin pabrik.',
            ],
        ];

        $barangInstances = [];
        foreach ($barangs as $item) {
            $barangInstances[$item['kode']] = Barang::updateOrCreate(['kode' => $item['kode']], $item);
        }

        // 3. Create Sample Peminjaman
        $peminjaman1 = Peminjaman::updateOrCreate(
            ['kode_transaksi' => 'PMJ-202610-0001'],
            [
                'user_id' => $karyawan->id,
                'barang_id' => $barangInstances['AST-QA-008']->id,
                'jumlah' => 1,
                'tgl_pinjam' => now()->subDays(2),
                'tgl_kembali_rencana' => now()->addDays(3),
                'tgl_diserahkan' => now()->subDays(2),
                'keperluan' => 'Inspeksi dimensi batch part turbin valve pesanan PT Petrokimia',
                'status' => 'diserahkan',
                'catatan_manager' => 'Disetujui. Pastikan kalibrasi nol dicek sebelum pengukuran.',
                'diverifikasi_oleh' => $manager->id,
                'catatan_staff' => 'Barang diserahkan lengkap dengan probe sensor dan sertifikat kalibrasi.',
                'diproses_oleh' => $accounting->id,
            ]
        );

        $peminjaman2 = Peminjaman::updateOrCreate(
            ['kode_transaksi' => 'PMJ-202610-0002'],
            [
                'user_id' => $karyawan->id,
                'barang_id' => $barangInstances['AST-TL-021']->id,
                'jumlah' => 1,
                'tgl_pinjam' => now()->subDay(),
                'tgl_kembali_rencana' => now()->addDays(1),
                'keperluan' => 'Perawatan berkala dan penggantian baut pada mesin casting foundry 1',
                'status' => 'diajukan',
                'catatan_manager' => null,
                'diverifikasi_oleh' => null,
            ]
        );

        $peminjaman3 = Peminjaman::updateOrCreate(
            ['kode_transaksi' => 'PMJ-202610-0003'],
            [
                'user_id' => $karyawan->id,
                'barang_id' => $barangInstances['AST-IT-019']->id,
                'jumlah' => 1,
                'tgl_pinjam' => now()->subDays(10),
                'tgl_kembali_rencana' => now()->subDays(3),
                'tgl_diserahkan' => now()->subDays(10),
                'tgl_kembali_aktual' => now()->subDays(3),
                'keperluan' => 'Konfigurasi parameter PLC inverter di lini produksi Machining',
                'status' => 'dikembalikan',
                'catatan_manager' => 'Disetujui untuk pembaruan software PLC.',
                'diverifikasi_oleh' => $manager->id,
                'catatan_staff' => 'Barang kembali dalam kondisi bersih dan normal.',
                'diproses_oleh' => $accounting->id,
                'kondisi_kembali' => 'baik',
            ]
        );

        // 4. Create Tracking Barang Entries
        TrackingBarang::create([
            'barang_id' => $barangInstances['AST-QA-008']->id,
            'peminjaman_id' => $peminjaman1->id,
            'lokasi_sekarang' => 'Bay 3 Workshop Machining (Station QA)',
            'status_posisi' => 'Sedang Digunakan Lapangan',
            'keterangan' => 'Dipinjam oleh Budi Santoso untuk pengukuran dimensi batch part turbin.',
            'diperbarui_oleh' => $accounting->id,
        ]);

        TrackingBarang::create([
            'barang_id' => $barangInstances['AST-LOG-005']->id,
            'peminjaman_id' => null,
            'lokasi_sekarang' => 'Bengkel Maintenance Divisi GA',
            'status_posisi' => 'Dalam Maintenance',
            'keterangan' => 'Pengecekan sistem hidrolik dan pengisian ulang baterai.',
            'diperbarui_oleh' => $accounting->id,
        ]);

        // 5. Create Sample Laporan
        Laporan::updateOrCreate(
            ['nomor_laporan' => 'LAP-INV-2026-10'],
            [
                'judul' => 'Laporan Rekapitulasi Inventaris & Peminjaman Periode Oktober 2026',
                'periode' => 'Oktober 2026',
                'tanggal_mulai' => now()->startOfMonth(),
                'tanggal_selesai' => now()->endOfMonth(),
                'status' => 'diajukan',
                'catatan_penyusun' => 'Seluruh aset aktif beroperasi dengan baik. Ada 1 unit forklift dalam proses perbaikan terjadwal.',
                'ringkasan_stok' => [
                    'total_aset' => 21,
                    'kondisi_baik' => 19,
                    'maintenance' => 2,
                    'rusak' => 0,
                ],
                'ringkasan_peminjaman' => [
                    'total_pengajuan' => 18,
                    'disetujui' => 16,
                    'selesai_kembali' => 14,
                    'sedang_berjalan' => 2,
                ],
                'disusun_oleh' => $manager->id,
                'direview_oleh' => $direktur->id,
            ]
        );
    }
}
