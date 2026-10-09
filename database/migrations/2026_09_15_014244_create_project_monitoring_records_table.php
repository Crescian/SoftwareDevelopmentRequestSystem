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
        Schema::create('project_monitoring_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('record_number', 50);

            $table->date('monitoring_date');

            $table->string('monitoring_type', 50);

            $table->decimal('uptime_percentage', 5, 2)->nullable();

            $table->decimal('average_response_time_ms', 12, 2)->nullable();

            $table->unsignedInteger('active_users')->nullable();

            $table->unsignedInteger('total_users')->nullable();

            $table->unsignedInteger('incidents_count')->default(0);

            $table->unsignedInteger('user_feedback_count')->default(0);

            $table->text('performance_observations')->nullable();

            $table->text('user_feedback_summary')->nullable();

            $table->text('improvement_actions')->nullable();

            $table->uuid('recorded_by');

            $table->timestamp('recorded_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'record_number'
            ]);

            $table->index([
                'software_project_id',
                'monitoring_date'
            ]);

            $table->index('monitoring_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_monitoring_records');
    }
};
