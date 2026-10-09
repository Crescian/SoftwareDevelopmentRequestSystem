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
        Schema::create('release_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('deployment_id')
                ->constrained('deployments')
                ->cascadeOnDelete();

            $table->string('document_number', 50);
            $table->string('title');

            $table->string('version', 50);

            $table->text('release_summary')->nullable();
            $table->text('new_features')->nullable();
            $table->text('bug_fixes')->nullable();
            $table->text('known_issues')->nullable();
            $table->text('breaking_changes')->nullable();

            $table->string('file_name')->nullable();
            $table->text('file_path')->nullable();

            $table->uuid('prepared_by');
            $table->timestamp('prepared_at')->nullable();

            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'deployment_id',
                'document_number',
                'version'
            ]);

            $table->index([
                'deployment_id',
                'version'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_notes');
    }
};
