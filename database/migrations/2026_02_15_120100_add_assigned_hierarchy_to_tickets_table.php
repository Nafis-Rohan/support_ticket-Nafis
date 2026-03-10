<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'assigned_to')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('tickets', 'assigned_hierarchy')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedInteger('assigned_hierarchy')->nullable()->after('assigned_to');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'assigned_hierarchy')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('assigned_hierarchy');
            });
        }

        if (Schema::hasColumn('tickets', 'assigned_to')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('assigned_to');
            });
        }
    }
};