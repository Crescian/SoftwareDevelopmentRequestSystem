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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('maintenance_number', 50);

            $table->string('maintenance_type', 30);

            $table->string('title');

            $table->text('description');

            $table->text('reason')->nullable();

            $table->string('priority', 30)->default('MEDIUM');

            $table->string('status', 30)->default('OPEN');

            $table->uuid('reported_by');
            $table->uuid('assigned_to')->nullable();

            $table->timestamp('reported_at')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('resolution')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'maintenance_number'
            ]);

            $table->index([
                'software_project_id',
                'status'
            ]);

            $table->index('maintenance_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
