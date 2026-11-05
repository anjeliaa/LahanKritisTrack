<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OBJECTID sumber BPDAS bisa sama untuk beberapa petak (poligon dipotong per kecamatan),
     * jadi tidak boleh unik. Diganti index biasa.
     */
    public function up(): void
    {
        Schema::table('data_lahan', function (Blueprint $table) {
            $table->dropUnique(['objectid']);
            $table->index('objectid');
        });
    }

    public function down(): void
    {
        Schema::table('data_lahan', function (Blueprint $table) {
            $table->dropIndex(['objectid']);
            $table->unique('objectid');
        });
    }
};
