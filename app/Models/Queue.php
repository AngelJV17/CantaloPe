<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_table_id',
        'song_id',
        'customer_name',
        'dedication',
        'type',
        'session_token',
        'payment_reference',
        'is_vip',
        'amount_paid',
        'status',
        'order_index',
        'started_at',
        'finished_at',
        'failed_reason',
    ];

    protected $casts = [
        'is_vip'      => 'boolean',
        'amount_paid' => 'decimal:2',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
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

    public function serviceTable(): BelongsTo
    {
        return $this->belongsTo(ServiceTable::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSongs(Builder $query): Builder
    {
        return $query->where('type', 'song');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'ready', 'playing']);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePlaying(Builder $query): Builder
    {
        return $query->where('status', 'playing');
    }

    public function scopePlayed(Builder $query): Builder
    {
        return $query->where('status', 'played');
    }

    public function scopeOrderedQueue(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPlaying(): bool
    {
        return $this->status === 'playing';
    }

    public function isPlayed(): bool
    {
        return $this->status === 'played';
    }

    public function markAsPlaying(): void
    {
        $this->update([
            'status'        => 'playing',
            'started_at'    => now(),
            'failed_reason' => null,
        ]);
    }

    public function markAsPlayed(): void
    {
        $this->update([
            'status'      => 'played',
            'finished_at' => now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status'        => 'failed',
            'failed_reason' => $reason,
            'finished_at'   => now(),
        ]);
    }
}
