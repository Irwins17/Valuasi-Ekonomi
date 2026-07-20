<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_coefficients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('value', 18, 4);
            $table->string('unit');
            $table->enum('type', ['carbon_sequestration', 'water_retention', 'biodiversity', 'other']);
            $table->string('source')->nullable();
            $table->year('year')->default(date('Y'));
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('type');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_coefficients');
    }
};
