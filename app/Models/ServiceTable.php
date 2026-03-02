<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'identifier', 'uuid', 'capacity',
        'zone', 'status', 'current_session_token', 'is_active',
    ];

    // Valores por defecto para que Eloquent sepa manejar los Enums
    protected $attributes = [
        'status'    => 'empty',
        'is_active' => true,
    ];

    protected static function booted()
    {
        static::creating(function ($table) {
            // Genera el UUID permanente para el QR físico
            $table->uuid = (string) Str::uuid();

            // Genera el token de sesión inicial
            $table->current_session_token = Str::random(40);
        });
    }

    /**
     * Lógica de "Limpieza/Reset" de Mesa
     * Esto invalida los QRs de clientes que ya se fueron.
     */
    public function release()
    {
        $this->update([
            'status'                => 'empty',
            'current_session_token' => Str::random(40), // Nuevo token = Link viejo muere
        ]);
    }

    // Relación: Una mesa tiene muchos pedidos en cola
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
