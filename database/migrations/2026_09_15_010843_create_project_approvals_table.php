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
        Schema::create('project_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('approval_type', 50);

            $table->uuid('approver_id');

            $table->string('status', 30)->default('PENDING');

            $table->text('remarks')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index([
                'software_project_id',
                'approval_type'
            ]);

            $table->index('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_approvals');
    }
};
