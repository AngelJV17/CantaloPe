<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',

        // 1. Identidad
        'local_name',
        'logo_path',
        'favicon_path',
        'description',

        // 2. Colores y Vibe
        'primary_color',
        'sidebar_color',
        'accent_color',
        'secondary_color',
        'text_color',

        // 3. Estética y Dark Mode
        'dark_mode',
        'border_radius',
        'font_family',

        // 4. Datos de Negocio
        'yape_number',
        'whatsapp_number',
    ];

    /**
     * Relación: La configuración pertenece a un Usuario (Dueño de local).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
