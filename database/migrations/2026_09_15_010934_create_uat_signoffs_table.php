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
        Schema::create('uat_signoffs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('uat_session_id')
                ->constrained('uat_sessions')
                ->cascadeOnDelete();

            $table->uuid('signatory_id');

            $table->string('signatory_name');

            $table->string('signatory_role', 100)->nullable();

            $table->string('decision', 30)
                ->default('PENDING');

            $table->text('remarks')->nullable();

            $table->timestamp('signed_at')->nullable();

            $table->timestamps();

            $table->index([
                'uat_session_id',
                'decision'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uat_signoffs');
    }
};
