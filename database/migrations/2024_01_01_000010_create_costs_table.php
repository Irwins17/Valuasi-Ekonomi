<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->enum('category', ['direct_cost', 'indirect_cost']);
            $table->enum('subcategory', ['investment', 'operation_maintenance', 'opportunity_cost', 'externality', 'other']);
            $table->string('description');
            $table->decimal('value', 18, 2);
            $table->string('payment_type')->nullable()->comment('Investasi Awal, Tahunan, etc');
            $table->year('year_applied')->nullable();
            $table->decimal('percentage_of_total', 5, 2)->nullable();
            $table->text('calculation_notes')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
