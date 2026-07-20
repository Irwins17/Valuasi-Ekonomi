<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->integer('respondent_id')->unique();
            $table->decimal('wtp', 15, 2)->comment('Willingness to Pay');
            $table->string('wtp_category')->nullable()->comment('Rendah, Sedang, Tinggi, dll');
            $table->integer('household_size')->nullable();
            $table->decimal('household_income', 15, 2)->nullable();
            $table->string('income_category')->nullable();
            $table->string('respondent_location')->nullable();
            $table->string('education_level')->nullable();
            $table->boolean('willing_to_pay')->default(true);
            $table->text('reason_if_unwilling')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('wtp_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvm_data');
    }
};
