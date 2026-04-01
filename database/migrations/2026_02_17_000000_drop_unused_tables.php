<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ticket_forward_logs');
        Schema::dropIfExists('category_engineer_hierarchy');
        Schema::dropIfExists('ticket_engineer');
    }

    public function down(): void
    {
        // Tables are not recreated in down() - restore from backup if needed
    }
};
