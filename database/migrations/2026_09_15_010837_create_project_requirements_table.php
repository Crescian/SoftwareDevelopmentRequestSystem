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
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('requirement_type', 30);
            $table->string('requirement_code', 50);
            $table->string('title');
            $table->text('description');

            $table->string('priority', 30)->nullable();
            $table->string('status', 30)->default('DRAFT');

            $table->uuid('created_by');

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'requirement_code'
            ]);

            $table->index('requirement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};
