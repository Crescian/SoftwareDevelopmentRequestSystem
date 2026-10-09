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
        Schema::create('development_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->foreignUuid('repository_id')
                ->nullable()
                ->constrained('repositories')
                ->nullOnDelete();

            $table->string('task_code', 50);
            $table->string('title');

            $table->text('description')->nullable();

            $table->string('task_type', 30)
                ->default('DEVELOPMENT');

            $table->string('priority', 30)
                ->default('MEDIUM');

            $table->string('status', 30)
                ->default('TODO');

            $table->uuid('assigned_to')->nullable();

            $table->uuid('created_by');

            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();

            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'task_code'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('development_tasks');
    }
};
