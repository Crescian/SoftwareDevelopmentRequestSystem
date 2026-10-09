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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('document_number', 50);
            $table->string('document_type', 50);
            $table->string('title');

            $table->string('version', 30)->default('1.0');

            $table->string('status', 30)->default('DRAFT');

            $table->date('document_date')->nullable();

            $table->text('description')->nullable();

            $table->string('file_name')->nullable();
            $table->text('file_path')->nullable();

            $table->uuid('prepared_by');
            $table->timestamp('prepared_at')->nullable();

            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'document_number',
                'version'
            ]);

            $table->index([
                'software_project_id',
                'document_type'
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
        Schema::dropIfExists('project_documents');
    }
};
