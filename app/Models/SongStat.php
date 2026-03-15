<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongStat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'song_id',
        'times_requested',
        'times_played',
        'times_failed',
        'last_requested_at',
        'last_played_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'times_requested'   => 'integer',
        'times_played'      => 'integer',
        'times_failed'      => 'integer',
        'last_requested_at' => 'datetime',
        'last_played_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeTopPlayed(Builder $query): Builder
    {
        return $query->orderByDesc('times_played');
    }

    public function scopeTopRequested(Builder $query): Builder
    {
        return $query->orderByDesc('times_requested');
    }

    public function scopeMostFailed(Builder $query): Builder
    {
        return $query->orderByDesc('times_failed');
    }

    public function scopeRecentlyPlayed(Builder $query): Builder
    {
        return $query->orderByDesc('last_played_at');
    }

    public function scopeColdSongs(Builder $query): Builder
    {
        return $query->orderBy('last_played_at');
    }

    /*
    |--------------------------------------------------------------------------
    | DOMAIN METHODS
    |--------------------------------------------------------------------------
    */

    public function markAsRequested(): void
    {
        $this->increment('times_requested');

        $this->forceFill([
            'last_requested_at' => now(),
        ])->save();
    }

    public function markAsPlayed(): void
    {
        $this->increment('times_played');

        $this->forceFill([
            'last_played_at' => now(),
        ])->save();
    }

    public function markAsFailed(): void
    {
        $this->increment('times_failed');
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPERS
    |--------------------------------------------------------------------------
    */

    public static function firstOrCreateFor(int $userId, int $songId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'song_id' => $songId,
            ],
            [
                'times_requested'   => 0,
                'times_played'      => 0,
                'times_failed'      => 0,
                'last_requested_at' => null,
                'last_played_at'    => null,
            ]
        );
    }
}
