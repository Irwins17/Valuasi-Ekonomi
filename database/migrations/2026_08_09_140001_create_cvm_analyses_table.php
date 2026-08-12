<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dichotomous-choice CVM model.
     *
     *   P(Ya) = 1 / [1 + exp(−(α − β·Ai + γk·Xki))]
     *   Mean WTP = (α + γk·X̄k) / β
     *   Total WTP = Mean WTP × N populasi
     *
     * As with TCM, coefficients are either estimated from the project's own
     * respondents or entered manually, and the fit's diagnostics travel with
     * the row so a published Total WTP can always be traced to the estimation
     * that produced it.
     */
    public function up(): void
    {
        Schema::create('cvm_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('analysis_code');
            $table->string('scenario')->comment('Skenario lingkungan / program');
            $table->enum('model', ['logit', 'probit'])->default('logit');
            $table->enum('coefficient_source', ['estimated', 'manual'])->default('estimated');
            $table->string('question_type')->default('Dichotomous Choice');

            $table->decimal('bid_value', 18, 2)->nullable()->comment('Ai — nilai tawaran acuan');
            $table->unsignedInteger('respondent_count')->default(0);
            $table->unsignedInteger('yes_count')->default(0);
            $table->unsignedInteger('no_count')->default(0);

            $table->decimal('alpha', 18, 8)->default(0)->comment('Konstanta α');
            $table->decimal('beta_bid', 18, 8)->default(0)->comment('Koefisien bid β');
            $table->decimal('coef_income', 18, 8)->nullable();
            $table->decimal('coef_education', 18, 8)->nullable();
            $table->decimal('coef_age', 18, 8)->nullable();
            $table->json('mean_covariates')->nullable()->comment('Rata-rata X̄k yang dipakai pada Mean WTP');

            $table->unsignedBigInteger('target_population')->default(0);
            $table->decimal('probability_yes', 10, 6)->default(0)->comment('Otomatis: P(Ya) pada bid acuan');
            $table->decimal('mean_wtp', 18, 2)->default(0)->comment('Otomatis: (α + γX̄) / β');
            $table->decimal('total_wtp', 18, 2)->default(0)->comment('Otomatis: Mean WTP × populasi');

            $table->boolean('converged')->nullable();
            $table->decimal('log_likelihood', 18, 6)->nullable();
            $table->json('diagnostics')->nullable();

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'analysis_code'], 'cvm_analysis_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvm_analyses');
    }
};
