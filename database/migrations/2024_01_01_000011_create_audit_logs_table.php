<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event')->comment('created, updated, deleted, approved, published');
            $table->string('model_type')->comment('Nama model yang diubah');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('table_name');
            $table->longText('changes')->nullable()->comment('JSON perubahan data');
            $table->longText('old_values')->nullable()->comment('JSON nilai lama');
            $table->longText('new_values')->nullable()->comment('JSON nilai baru');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('table_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
