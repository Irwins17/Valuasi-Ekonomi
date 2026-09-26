<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Uji Asumsi (flowchart Langkah 9) — documents and tests the key assumptions behind a project's valuation. */
    public function up(): void
    {
        Schema::create('project_assumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('assumption_type')->comment('Mis. Discount Rate, Elastisitas Harga, Umur Manfaat Ekosistem');
            $table->string('assumed_value')->comment('Nilai yang diasumsikan, mis. "10%" atau "20 tahun"');
            $table->text('justification')->comment('Dasar/justifikasi asumsi ini dipakai');
            $table->text('tested_result')->nullable()->comment('Hasil pengujian/analisis kepekaan terhadap asumsi ini');
            $table->enum('status', ['valid', 'perlu_revisi', 'tidak_valid'])->default('valid');
            $table->foreignId('tested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assumptions');
    }
};
