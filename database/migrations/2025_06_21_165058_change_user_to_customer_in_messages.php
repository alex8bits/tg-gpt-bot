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
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('customer_id')->after('id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('gpt_bot_id')->after('customer_id')->constrained('g_p_t_bots')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->dropForeign(['gpt_bot_id']);
            $table->dropColumn('gpt_bot_id');
        });
    }
};
