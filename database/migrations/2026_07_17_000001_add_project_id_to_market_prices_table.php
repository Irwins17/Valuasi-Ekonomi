<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_prices', function (Blueprint $table) {
            // NULL  = harga umum / global (berlaku untuk semua proyek/daerah)
            // terisi = harga spesifik untuk satu proyek valuasi tertentu
            $table->foreignId('project_id')
                ->nullable()
                ->after('id')
                ->constrained('projects')
                ->cascadeOnDelete();

            // Unique lama (commodity_name, unit, year) tidak lagi valid karena
            // sekarang komoditas yang sama boleh punya versi global + versi per-proyek.
            $table->dropUnique(['commodity_name', 'unit', 'year']);
            $table->unique(['project_id', 'commodity_name', 'unit', 'year'], 'market_prices_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('market_prices', function (Blueprint $table) {
            $table->dropUnique('market_prices_scope_unique');
            $table->dropConstrainedForeignId('project_id');
            $table->unique(['commodity_name', 'unit', 'year']);
        });
    }
};
