<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Validasi Stakeholder (flowchart Langkah 9) — simple record of stakeholder feedback on a project's valuation. */
    public function up(): void
    {
        Schema::create('stakeholder_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('stakeholder_name');
            $table->string('stakeholder_role')->nullable()->comment('Jabatan/instansi');
            $table->date('validation_date');
            $table->text('feedback')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'perlu_revisi'])->default('pending');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholder_validations');
    }
};
