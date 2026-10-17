<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entitas MONITORING.
     *  - dipantau : KEGIATAN_REHABILITASI 1 --- N MONITORING (id_kegiatan)
     *  - mencatat : USER 1 --- N MONITORING                  (id_user)
     */
    public function up(): void
    {
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id('id_monitoring');
            $table->foreignId('id_kegiatan')->constrained('kegiatan_rehabilitasi', 'id_kegiatan')->cascadeOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->nullOnDelete();
            $table->date('tanggal_monitoring');
            $table->unsignedTinyInteger('progres_persen')->default(0);
            $table->enum('kondisi_lahan', ['Membaik', 'Stabil', 'Memburuk'])->default('Stabil');
            $table->text('catatan')->nullable();
            $table->string('foto_dokumentasi')->nullable(); // path pada disk "public"
            $table->timestamps();

            $table->index(['id_kegiatan', 'tanggal_monitoring']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};
