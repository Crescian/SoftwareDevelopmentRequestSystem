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
        Schema::create('software_projects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('project_id', 30)->unique();
            $table->string('project_name');

            $table->foreignUuid('requesting_department_id')
                ->constrained('departments');

            $table->uuid('requestor_id');
            $table->uuid('project_sponsor_id');
            $table->uuid('project_manager_id')->nullable();

            $table->timestamp('date_submitted')->nullable();

            $table->text('project_summary');
            $table->text('business_problem');
            $table->text('proposed_solution');

            $table->string('status', 50)->default('DRAFT');
            $table->string('priority', 30)->nullable();

            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('requestor_id');
            $table->index('project_sponsor_id');
            $table->index('project_manager_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_projects');
    }
};
