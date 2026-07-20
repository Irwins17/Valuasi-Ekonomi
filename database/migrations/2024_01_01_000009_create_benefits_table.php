<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->enum('category', ['direct_use', 'indirect_use', 'non_use']);
            $table->enum('subcategory', ['production', 'tourism', 'recreation', 'water_regulation', 'carbon_sequestration', 'existence_value', 'bequest_value']);
            $table->string('description');
            $table->decimal('value', 18, 2);
            $table->string('method_used')->nullable()->comment('TCM, CVM, EOP, dll');
            $table->enum('data_source', ['eop', 'tcm', 'cvm', 'manual', 'literature']);
            $table->integer('sample_size')->nullable();
            $table->decimal('mean_value', 18, 2)->nullable();
            $table->decimal('percentage_of_tev', 5, 2)->nullable();
            $table->text('calculation_notes')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
