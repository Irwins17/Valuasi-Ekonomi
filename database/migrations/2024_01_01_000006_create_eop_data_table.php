<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eop_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('commodity_name');
            $table->decimal('production_before', 18, 2)->comment('Volume produksi sebelum (unit)');
            $table->decimal('production_after', 18, 2)->comment('Volume produksi sesudah (unit)');
            $table->decimal('production_change', 18, 2)->default(0)->comment('Perubahan volume produksi');
            $table->string('unit');
            $table->decimal('market_price', 18, 2)->comment('Harga pasar per unit');
            $table->decimal('total_value', 18, 2)->default(0)->comment('Total nilai (otomatis)');
            $table->enum('impact_type', ['positive', 'negative']);
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'impact_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eop_data');
    }
};
