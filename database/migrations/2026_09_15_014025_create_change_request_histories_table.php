<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('change_request_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('change_request_id')
                ->constrained('change_requests')
                ->cascadeOnDelete();

            $table->string('action', 50);

            $table->string('from_status', 30)->nullable();

            $table->string('to_status', 30)->nullable();

            $table->string('version', 30)->nullable();

            $table->text('change_summary')->nullable();

            $table->uuid('changed_by');

            $table->timestamp('changed_at');

            $table->timestamps();

            $table->index([
                'change_request_id',
                'changed_at'
            ]);

            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_request_histories');
    }
};
