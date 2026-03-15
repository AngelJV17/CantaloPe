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
        Schema::create('song_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('times_requested')->default(0);
            $table->unsignedInteger('times_played')->default(0);
            $table->unsignedInteger('times_failed')->default(0);

            $table->timestamp('last_requested_at')->nullable();
            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'song_id']);
            $table->index(['user_id', 'times_played']);
            $table->index(['user_id', 'times_requested']);
            $table->index(['user_id', 'last_played_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_stats');
    }
};
