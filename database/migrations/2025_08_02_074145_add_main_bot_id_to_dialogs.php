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
        Schema::table('dialogs', function (Blueprint $table) {
            $table->foreignId('main_bot_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dialogs', function (Blueprint $table) {
            $table->dropForeign(['main_bot_id']);
            $table->dropColumn('main_bot_id');
        });
    }
};
