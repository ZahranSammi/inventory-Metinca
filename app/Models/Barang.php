<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'stok_total',
        'stok_tersedia',
        'kondisi',
        'lokasi',
        'golongan_fiskal',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'stok_total' => 'integer',
        'stok_tersedia' => 'integer',
        'is_active' => 'boolean',
    ];

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function trackings()
    {
        return $this->hasMany(TrackingBarang::class);
    }

    public function getKondisiBadgeAttribute(): string
    {
        return match ($this->kondisi) {
            'baik' => 'success',
            'maintenance' => 'warning',
            'rusak_ringan' => 'danger',
            'rusak_berat' => 'dark',
            default => 'secondary',
        };
    }
}
