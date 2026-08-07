<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecosystem_land_covers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valuation_index_id')->constrained('ecosystem_valuation_indices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('area_ha', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['valuation_index_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecosystem_land_covers');
    }
};
