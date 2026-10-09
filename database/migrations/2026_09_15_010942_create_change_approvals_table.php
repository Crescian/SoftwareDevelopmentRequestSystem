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
        Schema::create('change_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('change_request_id')
                ->constrained('change_requests')
                ->cascadeOnDelete();

            $table->unsignedInteger('approval_sequence');

            $table->string('approval_type', 50);

            $table->uuid('approver_id');

            $table->string('status', 30)->default('PENDING');

            $table->text('remarks')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique([
                'change_request_id',
                'approval_sequence'
            ]);

            $table->index([
                'change_request_id',
                'status'
            ]);

            $table->index('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_approvals');
    }
};
