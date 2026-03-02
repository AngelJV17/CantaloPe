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
        Schema::create('service_tables', function (Blueprint $table) {
            $table->id();
            // Esto vincula la mesa con el dueño del Karaoke (User)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');
            // Por eso quitamos el ->unique() global y lo manejaremos por código o índice compuesto.
            $table->string('identifier');

            $table->uuid('uuid')->unique(); // Este sí es único universal para el QR

            $table->integer('capacity')->default(4);
            $table->string('zone')->default('General');

            $table->enum('status', [
                'empty', 'occupied', 'reserved', 'calling',
                'pending_payment', 'cleaning', 'inactive', 'blocked',
            ])->default('empty');

            $table->string('current_session_token')->nullable();
            $table->boolean('is_active')->default(true);

            // Índice compuesto: No puede haber dos "Mesa 1" para el mismo usuario
            $table->unique(['user_id', 'identifier']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tables');
    }
};
