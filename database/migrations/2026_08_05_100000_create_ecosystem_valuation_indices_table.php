<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Land-cover-based ecosystem service valuation (Jasa Ekosistem), following
 * the TEV-by-land-cover methodology in "Bahan Sistem Informasi VALEK":
 * value per line item = productivity/ha x price/unit x area (ha), grouped
 * into Provisioning/Regulating/Supporting/Cultural services and summed to a
 * TEV per land cover and per index. A project can have multiple indices
 * (e.g. Lampiran 1-4), each an independent full survey/assumption set over
 * a possibly different subset of land-cover types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecosystem_valuation_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->unsignedTinyInteger('index_number');
            $table->string('name');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['project_id', 'index_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecosystem_valuation_indices');
    }
};
