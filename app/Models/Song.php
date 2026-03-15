<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_seconds'     => 'integer',
        'tags'                 => 'array',
        'youtube_published_at' => 'datetime',
        'is_embeddable'        => 'boolean',
        'has_caption'          => 'boolean',
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
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', '%' . $term . '%')
                ->orWhere('artist', 'like', '%' . $term . '%')
                ->orWhere('youtube_title', 'like', '%' . $term . '%')
                ->orWhere('channel_title', 'like', '%' . $term . '%');
        });
    }

    public function scopeEmbeddable(Builder $query): Builder
    {
        return $query->where('is_embeddable', true);
    }

    public function scopeActivePrivacy(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('privacy_status')
                ->orWhere('privacy_status', 'public');
        });
    }

    public function scopeByArtist(Builder $query, string $artist): Builder
    {
        return $query->where('artist', $artist);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
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

    public function getDisplayArtistAttribute(): ?string
    {
        return $this->artist ?: $this->channel_title;
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /*
    |--------------------------------------------------------------------------
    | DOMAIN HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPublic(): bool
    {
        return is_null($this->privacy_status) || $this->privacy_status === 'public';
    }

    public function canBePlayed(): bool
    {
        return $this->is_embeddable && $this->isPublic();
    }
}
