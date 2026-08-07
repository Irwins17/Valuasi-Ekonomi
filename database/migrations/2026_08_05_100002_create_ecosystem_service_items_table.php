<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One line item of an ecosystem service valuation table (one species/product
 * row, e.g. "Cemara Laut" under Provisioning, or "Pencegah erosi" under
 * Regulating). `land_cover_id` is null for site-level Cultural items that
 * aren't tied to a specific land-cover polygon (e.g. "Pendidikan dan
 * Penelitian", "CSR"), matching how the source document reports them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecosystem_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valuation_index_id')->constrained('ecosystem_valuation_indices')->onDelete('cascade');
            $table->foreignId('land_cover_id')->nullable()->constrained('ecosystem_land_covers')->onDelete('cascade');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural']);
            $table->string('service_type');
            $table->string('item_name');
            $table->decimal('productivity_value', 18, 4)->nullable();
            $table->string('productivity_unit')->nullable();
            $table->decimal('unit_price', 18, 2)->nullable();
            $table->decimal('quantity', 18, 4)->nullable()->comment('Jumlah = productivity_value x unit_price');
            $table->decimal('total_value', 18, 2)->comment('Total Nilai Ekonomi');
            $table->text('source_note')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['valuation_index_id', 'service_category'], 'eco_service_items_index_category_idx');
            $table->index(['land_cover_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecosystem_service_items');
    }
};
