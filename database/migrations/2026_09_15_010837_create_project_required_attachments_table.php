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
        Schema::create('project_required_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('attachment_type', 50);

            $table->boolean('is_required')->default(true);

            $table->string('attachment_status', 30)
                ->default('NOT_ATTACHED');

            $table->string('file_name')->nullable();
            $table->text('file_path')->nullable();

            $table->string('document_title')->nullable();
            $table->string('document_version', 30)->nullable();
            $table->date('document_date')->nullable();

            $table->uuid('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();

            $table->uuid('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();

            $table->boolean('requester_signed_off')->default(false);

            $table->uuid('signed_off_by')->nullable();
            $table->timestamp('signed_off_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'software_project_id',
                'attachment_type'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_required_attachments');
    }
};
