<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('category_engineer_map')) {
            Schema::create('category_engineer_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                // One engineer can belong to only one category
                $table->unique(['user_id']);

                // Prevent duplicates
                $table->unique(['category_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_engineer_map');
    }
};

