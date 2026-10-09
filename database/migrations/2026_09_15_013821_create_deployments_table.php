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
        Schema::create('deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('deployment_number', 50);
            $table->string('version', 50);

            $table->string('environment', 30);
            $table->string('deployment_type', 30)->default('RELEASE');

            $table->string('status', 30)->default('PLANNED');

            $table->text('release_summary')->nullable();
            $table->text('changes_summary')->nullable();
            $table->text('rollback_plan')->nullable();

            $table->date('planned_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->uuid('prepared_by');
            $table->uuid('deployed_by')->nullable();
            $table->uuid('approved_by')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'deployment_number'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
