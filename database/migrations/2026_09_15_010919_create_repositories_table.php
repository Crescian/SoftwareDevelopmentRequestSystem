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
        Schema::create('repositories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('software_project_id')
                ->constrained('software_projects')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('provider', 30)->nullable();

            $table->text('repository_url');

            $table->string('default_branch', 100)
                ->default('main');

            $table->string('visibility', 30)
                ->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->uuid('created_by');

            $table->timestamps();

            $table->index([
                'software_project_id',
                'is_active'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
