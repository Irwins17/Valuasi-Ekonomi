<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (! Schema::hasColumn('administrative_boundaries', 'geojson')) {
            return;
        }

        Schema::table('administrative_boundaries', function (Blueprint $table) {
            $table->dropColumn('geojson');
        });
    }

    public function down(): void
    {
        Schema::table('administrative_boundaries', function (Blueprint $table) {
            $table->json('geojson')->nullable();
        });
    }
};
