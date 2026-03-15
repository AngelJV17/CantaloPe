<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * El usuario tiene una configuración de marca.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * El usuario (local) tiene su propia cola de canciones.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Un usuario (dueño de karaoke) tiene muchas mesas.
     */
    public function serviceTables(): HasMany
    {
        return $this->hasMany(ServiceTable::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->settings()->create([
                // Identidad
                'local_name'      => 'CANTALOPE',
                'description'     => null,
                'logo_path'       => null,
                'favicon_path'    => null,

                // Tema por defecto
                'theme_name'      => 'Neón',
                'theme_mode'      => 'dark',
                'is_custom_theme' => false,

                // Colores por defecto
                'primary_color'   => '#090a0f',
                'sidebar_color'   => '#12141c',
                'accent_color'    => '#6366f1',
                'secondary_color' => '#4338ca',
                'text_color'      => '#f3f4f6',

                // Estética
                'border_radius'   => '1rem',
                'font_family'     => 'Inter',

                // Negocio
                'yape_number'     => null,
                'whatsapp_number' => null,
            ]);
        });
    }
}
