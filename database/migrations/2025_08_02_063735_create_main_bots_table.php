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
        Schema::create('main_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_name');
            $table->foreignId('starting_bot')->nullable()->constrained('g_p_t_bots')->nullOnDelete();
            $table->text('prompt');
            $table->integer('rank')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_bots');
    }
};
