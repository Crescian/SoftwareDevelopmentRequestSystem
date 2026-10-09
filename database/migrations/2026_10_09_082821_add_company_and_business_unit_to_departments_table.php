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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignUuid('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->foreignUuid('business_unit_id')->nullable()->after('company_id')->constrained('business_units')->nullOnDelete();
            $table->timestamp('last_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('business_unit_id');
            $table->dropColumn('last_synced_at');
        });
    }
};
