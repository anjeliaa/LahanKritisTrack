<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel riwayat kekritisan per petak untuk tahun-tahun SEBELUM tahun data utama.
 * Tahun data utama (config lahan.tahun_data) selalu dibaca langsung dari tabel data_lahan,
 * sehingga tabel ini hanya berisi tahun-tahun sebelumnya dan tidak lagi memuat kolom sumber data.
 * Tabel dibuat ulang; isinya diisi kembali oleh DataLahanTemporalSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('data_lahan_temporal');

        Schema::create('data_lahan_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lahan');
            $table->unsignedSmallInteger('tahun');
            $table->string('kritis');
            $table->decimal('luas_ha', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_lahan')->references('id_lahan')->on('data_lahan')->onDelete('cascade');
            $table->unique(['id_lahan', 'tahun'], 'temporal_lahan_tahun_unique');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_lahan_temporal');
    }
};
