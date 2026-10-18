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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->integer('jumlah')->default(1);
            $table->date('tgl_pinjam');
            $table->date('tgl_kembali_rencana')->nullable();
            $table->timestamp('tgl_diserahkan')->nullable();
            $table->timestamp('tgl_kembali_aktual')->nullable();
            $table->text('keperluan');
            // diajukan -> disetujui_manager / ditolak_manager -> diserahkan -> dikembalikan
            $table->string('status')->default('diajukan'); 
            $table->text('catatan_manager')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_staff')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kondisi_kembali')->nullable(); // baik, maintenance, rusak_ringan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
