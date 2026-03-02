<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',       // El dueño del Karaoke
        'title', 
        'artist',        // Para búsquedas por cantante
        'youtube_id',
        'youtube_title', // Para mostrar el título real de YT
        'thumbnail_url', // Para la vista rápida
        'times_played',
        'last_played_at' // Para reportes de popularidad
    ];

    /**
     * Relación: Una canción pertenece a un dueño de Karaoke (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una canción puede estar en muchas colas (Queues)
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Lógica automática: Limpiar el título antes de guardar.
     * Si YouTube devuelve "MANA - RAYANDO EL SOL (KARAOKE)", 
     * esto lo deja bonito para la base de datos.
     */
    public function setTitleAttribute($value)
    {
        $clean = str_ireplace(['karaoke', '(karaoke)', '[karaoke]', 'official video'], '', $value);
        $this->attributes['title'] = trim($clean);
    }
}
