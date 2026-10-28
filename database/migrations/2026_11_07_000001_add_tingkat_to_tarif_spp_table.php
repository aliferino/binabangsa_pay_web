<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_spp', function (Blueprint $table) {
            $table->string('tingkat', 4)->nullable()->after('id_tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::table('tarif_spp', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });
    }
};