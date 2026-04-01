<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSolvedMessageToTicketsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tickets', 'solved_message')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->text('solved_message')->nullable()->after('solved_by');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tickets', 'solved_message')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('solved_message');
            });
        }
    }
}
