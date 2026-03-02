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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            // 1. Relación: ¿A qué dueño de Karaoke pertenece esta canción en su catálogo?
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 2. Identificador de YouTube (Fundamental)
            $table->string('youtube_id');
            $table->string('youtube_title')->nullable(); // Para guardar el título real de YT

            // 3. Datos que el Buscador de YT llenará automáticamente
            $table->string('title');              // Ej: "Mariposa Traicionera"
            $table->string('artist')->nullable(); // Ej: "Mana"

            // 4. URL de la miniatura (La guardamos para no procesarla cada vez)
            $table->string('thumbnail_url')->nullable();

            // 5. Estadísticas de uso en este local específico
            $table->integer('times_played')->default(0);
            $table->timestamp('last_played_at')->nullable(); // Para saber qué canciones están "frías"

            // 6. Seguridad: Evitar que el mismo dueño duplique la canción
            $table->unique(['user_id', 'youtube_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
