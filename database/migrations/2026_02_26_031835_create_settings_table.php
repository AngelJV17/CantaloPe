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
            $table->unique('user_id');

            // 1. Identidad
            $table->string('local_name')->default('CANTALOPE');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->text('description')->nullable();

            // 2. Tema visual
            $table->string('theme_name')->default('Neón');
            $table->string('theme_mode')->default('dark'); // dark | light
            $table->boolean('is_custom_theme')->default(false);

            // 3. Colores
            $table->string('primary_color', 7)->default('#090a0f');
            $table->string('sidebar_color', 7)->default('#12141c');
            $table->string('accent_color', 7)->default('#6366f1');
            $table->string('secondary_color', 7)->default('#4338ca');
            $table->string('text_color', 7)->default('#f3f4f6');

            // 4. Estética
            $table->string('border_radius')->default('1rem');
            $table->string('font_family')->default('Inter');

            // 5. Datos de negocio
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
