<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingBarang extends Model
{
    use HasFactory;

    protected $table = 'tracking_barangs';

    protected $fillable = [
        'barang_id',
        'peminjaman_id',
        'lokasi_sekarang',
        'status_posisi',
        'keterangan',
        'diperbarui_oleh',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'diperbarui_oleh');
    }
}
