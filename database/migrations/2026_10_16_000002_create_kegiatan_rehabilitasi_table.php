<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entitas KEGIATAN_REHABILITASI.
     *  - direhabilitasi : DATA_LAHAN 1 --- N KEGIATAN_REHABILITASI (id_lahan)
     *  - mengelola      : USER 1 --- N KEGIATAN_REHABILITASI       (id_user)
     */
    public function up(): void
    {
        Schema::create('kegiatan_rehabilitasi', function (Blueprint $table) {
            $table->id('id_kegiatan');
            $table->foreignId('id_lahan')->constrained('data_lahan', 'id_lahan')->restrictOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->nullOnDelete();
            $table->string('nama_kegiatan');
            $table->string('penanggung_jawab')->nullable();
            $table->string('jenis_kegiatan')->nullable();
            $table->decimal('target_luas', 12, 2)->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['Rencana', 'Berjalan', 'Selesai'])->default('Rencana');
            $table->date('tanggal_status')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_rehabilitasi');
    }
};
