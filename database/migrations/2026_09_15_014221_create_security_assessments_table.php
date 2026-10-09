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
        Schema::create('security_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('assessment_number', 50);

            $table->string('assessment_type', 50);

            $table->string('status', 30)->default('PLANNED');

            $table->text('authentication_review')->nullable();

            $table->text('authorization_review')->nullable();

            $table->text('data_privacy_review')->nullable();

            $table->text('vulnerability_assessment')->nullable();

            $table->text('security_risks')->nullable();

            $table->text('mitigation_actions')->nullable();

            $table->string('overall_result', 30)->nullable();

            $table->uuid('assessed_by');

            $table->timestamp('assessed_at')->nullable();

            $table->uuid('approved_by')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'assessment_number'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('assessment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_assessments');
    }
};
