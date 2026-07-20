<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->integer('respondent_id')->unique();
            $table->decimal('distance', 10, 2)->comment('Jarak dalam km');
            $table->decimal('transportation_cost', 15, 2);
            $table->decimal('time_cost', 15, 2)->comment('Nilai biaya waktu');
            $table->decimal('total_travel_cost', 15, 2)->default(0)->comment('Total perjalanan');
            $table->integer('visit_frequency')->comment('Frekuensi kunjungan per tahun');
            $table->decimal('consumer_surplus', 15, 2)->default(0)->comment('Surplus konsumen per responden');
            $table->string('origin_location')->nullable();
            $table->string('respondent_category')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcm_data');
    }
};
