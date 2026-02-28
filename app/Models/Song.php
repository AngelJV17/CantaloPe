<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'youtube_id',
        'times_played',
    ];

    /**
     * Relación: Una canción puede estar muchas veces en diferentes colas.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
