<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Direct Use Value entries.
     *
     *   gross_value = quantity × market_price
     *   net_value   = gross_value − production_cost
     *
     * The project-level DUV is the sum over rows, which is what
     * Σ(Qi × Pi) and Σ(Qi × Pi) − Ci mean in the spec.
     */
    public function up(): void
    {
        Schema::create('duv_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('record_code')->comment('ID Data');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->default('provisioning');
            $table->string('goods_type')->comment('Jenis barang / jasa');
            $table->string('location')->comment('Lokasi / ekosistem');

            $table->decimal('quantity', 18, 4)->default(0)->comment('Qi — kuantitas per periode');
            $table->string('unit')->nullable();
            $table->decimal('market_price', 18, 2)->default(0)->comment('Pi — harga pasar per unit');
            $table->decimal('production_cost', 18, 2)->default(0)->comment('Ci — biaya produksi/pengambilan');

            $table->decimal('gross_value', 18, 2)->default(0)->comment('Otomatis: Qi × Pi');
            $table->decimal('net_value', 18, 2)->default(0)->comment('Otomatis: gross − Ci');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->enum('data_status', ['draft', 'verified', 'final'])->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'data_status']);
            $table->unique(['project_id', 'record_code'], 'duv_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duv_data');
    }
};
