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
        Schema::create('test_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('test_plan_number', 50);
            $table->string('title');

            $table->string('version', 30)->default('1.0');

            $table->string('status', 30)
                ->default('DRAFT');

            $table->text('objectives')->nullable();
            $table->text('scope')->nullable();
            $table->text('test_strategy')->nullable();

            $table->string('test_environment', 100)->nullable();

            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();

            $table->uuid('prepared_by');
            $table->timestamp('prepared_at')->nullable();

            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'test_plan_number',
                'version'
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
        Schema::dropIfExists('test_plans');
    }
};
