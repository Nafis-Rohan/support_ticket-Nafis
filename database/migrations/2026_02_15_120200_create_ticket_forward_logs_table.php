<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ticket_forward_logs')) {
            Schema::create('ticket_forward_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedBigInteger('from_user_id')->nullable();
                $table->unsignedBigInteger('to_user_id');
                $table->unsignedInteger('from_hierarchy')->nullable();
                $table->unsignedInteger('to_hierarchy');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_forward_logs');
    }
};