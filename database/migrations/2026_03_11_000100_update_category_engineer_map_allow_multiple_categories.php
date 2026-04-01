<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('category_engineer_map')) {
            Schema::table('category_engineer_map', function (Blueprint $table) {
                // Remove "one engineer only one category" constraint
                // Default Laravel index name: {table}_{column}_unique
                $table->dropUnique('category_engineer_map_user_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('category_engineer_map')) {
            Schema::table('category_engineer_map', function (Blueprint $table) {
                $table->unique(['user_id']);
            });
        }
    }
};

