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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 1. Identidad
            $table->string('local_name')->default('CANTALOPE');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->text('description')->nullable();

                                                                      // 2. Colores y Vibe
            $table->string('primary_color', 7)->default('#090a0f');   // Fondo general
            $table->string('sidebar_color', 7)->default('#12141c');   // Color del menú
            $table->string('accent_color', 7)->default('#6366f1');    // Color de destaque (Indigo)
            $table->string('secondary_color', 7)->default('#4338ca'); // Color para gradientes/hovers
            $table->string('text_color', 7)->default('#f3f4f6');      // Color de letra

                                                         // 3. Estética y Dark Mode
            $table->boolean('dark_mode')->default(true); // Tu recomendación: activo por defecto
            $table->string('border_radius')->default('1rem');
            $table->string('font_family')->default('Inter');

            // 4. Datos de Negocio
            $table->string('yape_number', 20)->nullable();
            $table->string('whatsapp_number', 20)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
