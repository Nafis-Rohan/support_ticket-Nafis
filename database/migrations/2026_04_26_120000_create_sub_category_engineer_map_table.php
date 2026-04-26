<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_category_engineer_map')) {
            Schema::create('sub_category_engineer_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sub_category_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['sub_category_id', 'user_id']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_category_engineer_map');
    }
};

