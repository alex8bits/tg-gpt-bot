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
        Schema::table('g_p_t_bots', function (Blueprint $table) {
            $table->text('system_request')->nullable()->after('prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_p_t_bots', function (Blueprint $table) {
            $table->dropColumn('system_request');
        });
    }
};
