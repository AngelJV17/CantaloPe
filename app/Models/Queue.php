<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'service_table_id', 'song_id',
        'customer_name', 'dedication', 'type',
        'session_token', 'payment_reference', 'is_vip',
        'amount_paid', 'status', 'order_index',
    ];

    // Casting para asegurar que los números se manejen correctamente
    protected $casts = [
        'is_vip'      => 'boolean',
        'amount_paid' => 'decimal:2',
        'order_index' => 'integer',
    ];

    // Relación con la Mesa
    public function serviceTable()
    {
        return $this->belongsTo(ServiceTable::class);
    }

    // Relación con la Canción (puede ser null si es un pedido de trago)
    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Scope para el DJ: Solo ver canciones pendientes ordenadas por VIP
     */
    public function scopePendingKaraoke($query)
    {
        return $query->where('type', 'song')
            ->where('status', 'pending')
            ->orderBy('is_vip', 'desc')
            ->orderBy('order_index', 'asc')
            ->orderBy('created_at', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
