<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Completes the CVM respondent form: the elicitation design (scenario,
     * WTP vs WTA, question format, bid amount) and the covariates the
     * logit/probit model needs.
     *
     * The existing `wtp` column keeps holding the respondent's stated amount
     * and `willing_to_pay` their yes/no answer, so nothing recorded so far
     * changes meaning. `bid_amount` is what makes dichotomous-choice analysis
     * possible at all — without the offered price a yes/no answer carries no
     * information about the value.
     */
    public function up(): void
    {
        Schema::table('cvm_data', function (Blueprint $table) {
            $table->string('scenario')->nullable()->after('respondent_location')
                ->comment('Skenario / program lingkungan');
            $table->enum('valuation_type', ['wtp', 'wta'])->default('wtp')->after('scenario');
            $table->enum('question_method', ['dichotomous_choice', 'open_ended', 'payment_card'])
                ->default('open_ended')->after('valuation_type');
            $table->decimal('bid_amount', 15, 2)->nullable()->after('question_method')
                ->comment('Nilai tawaran yang disodorkan ke responden');
            $table->unsignedSmallInteger('age')->nullable()->after('household_income');
            $table->string('occupation')->nullable()->after('education_level')
                ->comment('Pekerjaan / kategori responden');
        });
    }

    public function down(): void
    {
        Schema::table('cvm_data', function (Blueprint $table) {
            $table->dropColumn([
                'scenario', 'valuation_type', 'question_method',
                'bid_amount', 'age', 'occupation',
            ]);
        });
    }
};
