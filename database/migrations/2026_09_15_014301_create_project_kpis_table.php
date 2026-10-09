<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('kpi_code', 50);

            $table->string('kpi_name');

            $table->string('measurement_period', 50)->nullable();

            $table->decimal('target_value', 15, 2)->nullable();

            $table->decimal('actual_value', 15, 2)->nullable();

            $table->string('unit', 30)->nullable();

            $table->string('status', 30)->nullable();

            $table->text('calculation_method')->nullable();

            $table->text('remarks')->nullable();

            $table->uuid('recorded_by');

            $table->timestamp('recorded_at')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'kpi_code',
                'measurement_period'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_kpis');
    }
};
