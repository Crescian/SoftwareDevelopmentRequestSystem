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
        Schema::create('defects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('test_case_id')
                ->constrained('test_cases')
                ->cascadeOnDelete();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('defect_number', 50);

            $table->string('title');

            $table->text('description');

            $table->string('severity', 30)
                ->default('MEDIUM');

            $table->string('priority', 30)
                ->default('MEDIUM');

            $table->string('status', 30)
                ->default('OPEN');

            $table->uuid('reported_by');

            $table->timestamp('reported_at')->nullable();

            $table->uuid('assigned_to')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->text('resolution')->nullable();

            $table->uuid('verified_by')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'defect_number'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defects');
    }
};
