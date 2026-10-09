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
        Schema::create('test_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('test_plan_id')
                ->constrained('test_plans')
                ->cascadeOnDelete();

            $table->string('test_case_number', 50);

            $table->string('title');

            $table->text('description')->nullable();

            $table->string('test_type', 30);

            $table->text('preconditions')->nullable();

            $table->text('test_steps');

            $table->text('expected_result');

            $table->text('actual_result')->nullable();

            $table->string('status', 30)
                ->default('NOT_EXECUTED');

            $table->uuid('executed_by')->nullable();

            $table->timestamp('executed_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'test_plan_id',
                'test_case_number'
            ]);

            $table->index([
                'test_plan_id',
                'status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
