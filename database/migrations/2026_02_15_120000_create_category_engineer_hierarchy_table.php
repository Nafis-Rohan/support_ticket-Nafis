<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('category_engineer_hierarchy')) {
            Schema::create('category_engineer_hierarchy', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('hierarchy');
                $table->timestamps();
                $table->unique(['category_id', 'hierarchy']);
                $table->unique(['category_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_engineer_hierarchy');
    }
};