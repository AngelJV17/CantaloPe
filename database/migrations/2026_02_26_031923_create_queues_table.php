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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Dueño del Karaoke
            $table->foreignId('song_id')->constrained();
            $table->string('customer_name');
            $table->string('dedication')->nullable();
            $table->boolean('is_vip')->default(false); // Si pagó Yape por saltar
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->enum('status', ['pending', 'playing', 'played'])->default('pending');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
