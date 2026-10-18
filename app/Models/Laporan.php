<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_laporan',
        'judul',
        'periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan_penyusun',
        'catatan_revisi',
        'ringkasan_stok',
        'ringkasan_peminjaman',
        'disusun_oleh',
        'direview_oleh',
        'tgl_review',
        'tgl_arsip',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tgl_review' => 'datetime',
        'tgl_arsip' => 'datetime',
        'ringkasan_stok' => 'array',
        'ringkasan_peminjaman' => 'array',
    ];

    public function penyusun()
    {
        return $this->belongsTo(User::class, 'disusun_oleh');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'direview_oleh');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'disusun' => ['label' => 'Draft Disusun', 'color' => 'secondary'],
            'diajukan' => ['label' => 'Menunggu Pemeriksaan Direktur', 'color' => 'warning'],
            'direvisi' => ['label' => 'Perlu Revisi', 'color' => 'danger'],
            'disetujui' => ['label' => 'Disetujui Direktur', 'color' => 'info'],
            'diarsipkan' => ['label' => 'Telah Diarsipkan', 'color' => 'success'],
            default => ['label' => $this->status, 'color' => 'light'],
        };
    }
}
