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
        Schema::create('uat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('uat_number', 50);

            $table->string('title');

            $table->string('version', 30)->nullable();

            $table->string('status', 30)
                ->default('PLANNED');

            $table->date('planned_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->text('scope')->nullable();

            $table->text('environment')->nullable();

            $table->text('summary')->nullable();

            $table->uuid('facilitated_by')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'uat_number'
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
        Schema::dropIfExists('uat_sessions');
    }
};
