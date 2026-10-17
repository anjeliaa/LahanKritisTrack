<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entitas DATA_LAHAN pada ERD: satu baris = satu petak lahan (polygon).
     */
    public function up(): void
    {
        Schema::create('data_lahan', function (Blueprint $table) {
            $table->id('id_lahan');
            $table->unsignedInteger('objectid')->nullable()->unique(); // OBJECTID dari sumber data spasial
            $table->string('wilayah')->nullable();
            $table->unsignedTinyInteger('kecamatan_id')->index();
            $table->string('kecamatan');
            $table->string('bpdas')->default('Ketahun');
            $table->string('kritis'); // label: Tidak Kritis ... Sangat Kritis
            $table->decimal('luas_ha', 12, 2)->default(0);
            $table->json('geometri'); // geometry GeoJSON (Polygon / MultiPolygon)
            $table->timestamps();

            $table->index('kritis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_lahan');
    }
};
