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

            // Relación principal: a qué karaoke pertenece el pedido
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Mesa desde donde se hizo el pedido
            $table->foreignId('service_table_id')->nullable()->constrained()->nullOnDelete();

            // Canción solicitada
            $table->foreignId('song_id')->nullable()->constrained()->nullOnDelete();

            // Datos del cliente
            $table->string('customer_name');
            $table->text('dedication')->nullable();

            // Tipo de pedido
            $table->string('type')->default('song'); // song | product | service

            // Control de sesión / pagos
            $table->string('session_token')->nullable();
            $table->string('payment_reference')->nullable();

            // Prioridad / monetización
            $table->boolean('is_vip')->default(false);
            $table->decimal('amount_paid', 10, 2)->default(0);

            // Estado de la cola
            $table->string('status')->default('pending'); // pending | ready | playing | played | failed

            // Orden de reproducción
            $table->unsignedInteger('order_index')->default(0);

            // Tiempos operativos
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Si una canción falla o se omite por error técnico
            $table->string('failed_reason')->nullable();

            $table->timestamps();

            // Índices operativos
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type', 'status']);
            $table->index(['user_id', 'order_index']);
            $table->index(['service_table_id', 'status']);
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
