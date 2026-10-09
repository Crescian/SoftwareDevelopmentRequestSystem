<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `dashboard_role` is a local override, not part of the ticketing
     * system's identity payload — see TicketingIdentityService::syncLocalUser,
     * which never writes to it. It decides which dashboard a user lands on
     * since `role`/`department` are free-text and unpredictable.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dashboard_role', 30)->nullable()->after('department');
            $table->index('dashboard_role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['dashboard_role']);
            $table->dropColumn('dashboard_role');
        });
    }
};
