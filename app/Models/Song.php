<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'youtube_id',
        'youtube_title',
        'title',
        'artist',
        'channel_title',
        'thumbnail_url',
        'duration_seconds',
        'category_id',
        'tags',
        'youtube_published_at',
        'is_embeddable',
        'privacy_status',
        'definition',
        'has_caption',
    ];

    protected $casts = [
        'tags'                 => 'array',
        'youtube_published_at' => 'datetime',
        'is_embeddable'        => 'boolean',
        'has_caption'          => 'boolean',
        'duration_seconds'     => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(SongStat::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', '%' . $term . '%')
                ->orWhere('artist', 'like', '%' . $term . '%')
                ->orWhere('youtube_title', 'like', '%' . $term . '%')
                ->orWhere('channel_title', 'like', '%' . $term . '%')
                ->orWhere('youtube_id', 'like', '%' . $term . '%');
        });
    }

    public function scopeEmbeddable(Builder $query): Builder
    {
        return $query->where('is_embeddable', true);
    }

    public function scopeActivePrivacy(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('privacy_status')
                ->orWhere('privacy_status', 'public')
                ->orWhere('privacy_status', 'unlisted');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: ($this->youtube_title ?: 'Sin título');
    }

    public function getDisplayArtistAttribute(): string
    {
        return $this->artist ?: ($this->channel_title ?: 'Artista desconocido');
    }
}
