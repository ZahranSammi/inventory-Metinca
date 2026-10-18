<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'barang_id',
        'jumlah',
        'tgl_pinjam',
        'tgl_kembali_rencana',
        'tgl_diserahkan',
        'tgl_kembali_aktual',
        'keperluan',
        'status',
        'catatan_manager',
        'diverifikasi_oleh',
        'catatan_staff',
        'diproses_oleh',
        'kondisi_kembali',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_kembali_rencana' => 'date',
        'tgl_diserahkan' => 'datetime',
        'tgl_kembali_aktual' => 'datetime',
        'jumlah' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function staffAccounting()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function trackings()
    {
        return $this->hasMany(TrackingBarang::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'diajukan' => ['label' => 'Menunggu Verifikasi Manager', 'color' => 'warning', 'icon' => 'bi-hourglass-split'],
            'disetujui' => ['label' => 'Disetujui Manager (Siap Diambil)', 'color' => 'info', 'icon' => 'bi-check-circle'],
            'ditolak' => ['label' => 'Ditolak Manager', 'color' => 'danger', 'icon' => 'bi-x-circle'],
            'diserahkan' => ['label' => 'Sedang Dipinjam (Diserahkan)', 'color' => 'primary', 'icon' => 'bi-box-arrow-right'],
            'dikembalikan' => ['label' => 'Selesai (Dikembalikan)', 'color' => 'success', 'icon' => 'bi-check2-all'],
            default => ['label' => $this->status, 'color' => 'secondary', 'icon' => 'bi-question-circle'],
        };
    }
}
