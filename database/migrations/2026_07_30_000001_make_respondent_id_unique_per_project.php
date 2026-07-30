<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * respondent_id was globally unique across tcm_data/cvm_data, which meant two
 * different projects could never both have a respondent #1, #2, etc. Scope
 * the uniqueness to (project_id, respondent_id) instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tcm_data', function (Blueprint $table) {
            $table->dropUnique(['respondent_id']);
            $table->unique(['project_id', 'respondent_id']);
        });

        Schema::table('cvm_data', function (Blueprint $table) {
            $table->dropUnique(['respondent_id']);
            $table->unique(['project_id', 'respondent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tcm_data', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'respondent_id']);
            $table->unique('respondent_id');
        });

        Schema::table('cvm_data', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'respondent_id']);
            $table->unique('respondent_id');
        });
    }
};
