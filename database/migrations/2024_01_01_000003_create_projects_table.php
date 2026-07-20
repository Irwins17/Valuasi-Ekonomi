<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'published'])->default('draft');
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->decimal('tev', 18, 2)->nullable()->comment('Total Economic Value');
            $table->decimal('total_benefits', 18, 2)->nullable();
            $table->decimal('total_costs', 18, 2)->nullable();
            $table->decimal('bcr', 10, 4)->nullable()->comment('Benefit Cost Ratio');
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
