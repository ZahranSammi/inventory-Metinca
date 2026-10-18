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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('kategori'); // Machining, Casting, Quality Assurance, IT & Database, Perkakas, Kendaraan
            $table->integer('stok_total')->default(1);
            $table->integer('stok_tersedia')->default(1);
            $table->string('kondisi')->default('baik'); // baik, maintenance, rusak_ringan, rusak_berat
            $table->string('lokasi'); // Workshop Machining, Warehouse PPIC, QC Room, Gudang A, dll
            $table->string('golongan_fiskal')->nullable(); // Golongan 1, Golongan 2, Golongan 3, Bangunan
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
