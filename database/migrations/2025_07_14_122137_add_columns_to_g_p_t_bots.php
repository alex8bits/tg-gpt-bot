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
            $table->foreignId('category_id')->after('id')->nullable()->constrained()->nullOnDelete();
            $table->integer('rank')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_p_t_bots', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id', 'rank');
        });
    }
};
