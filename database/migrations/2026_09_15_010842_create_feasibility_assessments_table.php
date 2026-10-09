<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feasibility_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->uuid('assessed_by');

            $table->text('technical_feasibility')->nullable();
            $table->text('operational_feasibility')->nullable();
            $table->text('cost_benefit_analysis')->nullable();

            $table->string('overall_recommendation', 40)->nullable();

            $table->text('assessment_notes')->nullable();

            $table->timestamp('assessed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feasibility_assessments');
    }
};
