<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Travel-cost demand model, kept separate from the respondent rows it is
     * estimated from so a site can hold several competing specifications.
     *
     *   ln Vij = β₀ + β₁·TCij + γk·Xkij + εij
     *   CS per individu = −1 / β₁
     *   Nilai rekreasi   = CS × total pengunjung
     *
     * Coefficients may be estimated from the project's TCM respondents
     * (`coefficient_source` = 'estimated') or entered by hand when the model
     * was fitted elsewhere ('manual'). Estimation diagnostics are stored
     * alongside so a figure can always be traced back to the fit that
     * produced it — including whether that fit converged.
     */
    public function up(): void
    {
        Schema::create('tcm_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('analysis_code');
            $table->string('site_name')->comment('Lokasi wisata');
            $table->enum('regression_model', ['poisson', 'negative_binomial'])->default('poisson');
            $table->enum('coefficient_source', ['estimated', 'manual'])->default('estimated');

            $table->unsignedInteger('respondent_count')->default(0);
            $table->unsignedBigInteger('total_visitors')->default(0)->comment('Total pengunjung per tahun');
            $table->decimal('mean_travel_cost', 18, 2)->default(0)->comment('Rata-rata TCij');

            $table->decimal('beta_0', 18, 8)->default(0)->comment('Konstanta β₀');
            $table->decimal('beta_1', 18, 8)->default(0)->comment('Koefisien biaya perjalanan β₁');
            $table->decimal('coef_income', 18, 8)->nullable();
            $table->decimal('coef_age', 18, 8)->nullable();
            $table->decimal('coef_education', 18, 8)->nullable();
            $table->decimal('coef_substitute', 18, 8)->nullable();

            $table->decimal('consumer_surplus', 18, 2)->default(0)->comment('Otomatis: −1 / β₁');
            $table->decimal('recreation_value', 18, 2)->default(0)->comment('Otomatis: CS × total pengunjung');

            $table->string('estimation_method')->nullable();
            $table->boolean('converged')->nullable()->comment('null = belum diestimasi dari data');
            $table->decimal('log_likelihood', 18, 6)->nullable();
            $table->decimal('dispersion_alpha', 18, 8)->nullable()->comment('α negative binomial');
            $table->json('diagnostics')->nullable()->comment('Variabel yang dipakai, SE, catatan estimasi');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'analysis_code'], 'tcm_analysis_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcm_analyses');
    }
};
