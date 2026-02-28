<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'song_id',
        'customer_name',
        'dedication',
        'is_vip',
        'amount_paid',
        'status',
        'order_index',
    ];

    /**
     * Relación: El pedido pertenece a un local específico.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: El pedido contiene una canción específica.
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Método útil: Scope para obtener solo la cola pendiente del local actual.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->orderBy('is_vip', 'desc')->orderBy('order_index', 'asc');
    }
}
