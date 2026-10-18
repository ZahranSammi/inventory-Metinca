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
        Schema::create('tracking_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->foreignId('peminjaman_id')->nullable()->constrained('peminjamans')->nullOnDelete();
            $table->string('lokasi_sekarang');
            $table->string('status_posisi'); // Di Gudang, Sedang Digunakan Lapangan, Dalam Maintenance, Kembali ke Rak
            $table->text('keterangan')->nullable();
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_barangs');
    }
};
