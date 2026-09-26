<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Benefit Transfer entries (Langkah 6) — a value estimated elsewhere,
     * adjusted and scaled to this project's site rather than measured fresh.
     *
     *   transferred_value = source_value × adjustment_factor × target_quantity
     */
    public function up(): void
    {
        Schema::create('btm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('record_code')->comment('ID Data');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->default('regulating');

            $table->string('source_study_title');
            $table->string('source_study_location')->nullable();
            $table->unsignedSmallInteger('source_study_year')->nullable();
            $table->decimal('source_value', 18, 2)->default(0)->comment('Nilai asli studi sumber, per unit');

            $table->decimal('adjustment_factor', 10, 4)->default(1)->comment('Faktor penyesuaian (indeks harga/PPP/pendapatan)');
            $table->decimal('target_quantity', 18, 4)->default(1)->comment('Kuantitas/luas di lokasi target');

            $table->decimal('transferred_value', 18, 2)->default(0)->comment('Otomatis: source_value × faktor × kuantitas target');
            $table->text('validity_notes')->nullable()->comment('Catatan validitas transfer (kesesuaian konteks, batasan)');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->enum('data_status', ['draft', 'verified', 'final'])->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'data_status']);
            $table->unique(['project_id', 'record_code'], 'btm_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('btm_data');
    }
};
