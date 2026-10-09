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
        Schema::create('change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('change_request_number', 50);

            $table->string('title');

            $table->text('description');

            $table->text('reason');

            $table->text('business_justification')->nullable();

            $table->text('technical_impact')->nullable();

            $table->text('operational_impact')->nullable();

            $table->text('security_impact')->nullable();

            $table->text('data_impact')->nullable();

            $table->text('cost_impact')->nullable();

            $table->text('schedule_impact')->nullable();

            $table->text('risk_assessment')->nullable();

            $table->text('implementation_plan')->nullable();

            $table->text('rollback_plan')->nullable();

            $table->string('priority', 30)->default('MEDIUM');

            $table->string('status', 30)->default('DRAFT');

            $table->uuid('requested_by');

            $table->timestamp('requested_at')->nullable();

            $table->date('planned_implementation_date')->nullable();

            $table->timestamp('implemented_at')->nullable();

            $table->timestamp('closed_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'change_request_number'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
