<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_laporan')->unique();
            $table->string('judul');
            $table->string('periode'); // e.g. Oktober 2026
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            // disusun -> diajukan -> disetujui_direktur / direvisi -> diarsipkan
            $table->string('status')->default('disusun'); 
            $table->text('catatan_penyusun')->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->json('ringkasan_stok')->nullable();
            $table->json('ringkasan_peminjaman')->nullable();
            $table->foreignId('disusun_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('direview_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tgl_review')->nullable();
            $table->timestamp('tgl_arsip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
