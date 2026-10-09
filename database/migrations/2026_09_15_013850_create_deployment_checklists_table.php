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
        Schema::create('deployment_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('deployment_id')
                ->constrained('deployments')
                ->cascadeOnDelete();

            $table->unsignedInteger('item_no');

            $table->string('category', 50);

            $table->string('item');

            $table->boolean('is_required')->default(true);

            $table->string('status', 30)->default('PENDING');

            $table->text('remarks')->nullable();

            $table->uuid('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();

            $table->timestamps();

            $table->unique([
                'deployment_id',
                'item_no'
            ]);

            $table->index([
                'deployment_id',
                'status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_checklists');
    }
};
