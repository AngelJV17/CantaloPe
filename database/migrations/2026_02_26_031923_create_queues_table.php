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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_table_id')->constrained('service_tables')->onDelete('cascade');

            // Hacemos song_id nulable porque si piden una Pizza, no habrá canción
            $table->foreignId('song_id')->nullable()->constrained('songs')->onDelete('set null');

            $table->string('customer_name');
            $table->string('dedication')->nullable();

            // --- NUEVAS COLUMNAS ESTRATÉGICAS ---
            $table->enum('type', ['song', 'product', 'service'])->default('song');
            $table->string('session_token')->nullable();     // Para validar que el cliente está físicamente ahí
            $table->string('payment_reference')->nullable(); // ID de Yape/Plin para validación rápida
                                                             // ------------------------------------

            $table->boolean('is_vip')->default(false);
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->enum('status', [
                'pending',   // En cola de espera
                'ready',     // El DJ ya le dio "check" para que sea el siguiente
                'playing',   // Actualmente en pantalla
                'played',    // Canción terminada con éxito
                'skipped',   // Se saltó (ej: el cliente se fue al baño)
                'cancelled', // Eliminada por el Admin o Cliente
                'failed',    // El video no cargó o dio error de copyright
            ])->default('pending');
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
