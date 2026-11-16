
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_lahan_temporal', function (Blueprint $table) {
            $table->id();

            // Relasi ke petak lahan
            $table->unsignedBigInteger('id_lahan');

            // Tahun pengamatan
            $table->unsignedSmallInteger('tahun')->index();

            // Kondisi kekritisan lahan
            $table->string('kritis');

            // Luas lahan pada tahun tersebut
            $table->decimal('luas_ha', 12, 2)->default(0);

            // Sumber data: asli atau dummy
            $table->enum('data_source', ['asli', 'dummy'])
                  ->default('dummy');

            $table->timestamps();

            // Relasi ke tabel data_lahan
            $table->foreign('id_lahan')
                  ->references('id_lahan')
                  ->on('data_lahan')
                  ->onDelete('cascade');

            // Mencegah data petak dan tahun yang sama
            // untuk sumber yang sama
            $table->unique(
                ['id_lahan', 'tahun', 'data_source'],
                'temporal_lahan_tahun_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_lahan_temporal');
    }
};
