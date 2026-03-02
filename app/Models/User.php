<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    /**
     * Relación: El usuario tiene una configuración de marca.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->settings()->create([
                'local_name'      => 'CANTALOPE', // O 'Karaoke de ' . $user->name
                'primary_color'   => '#090a0f',
                'sidebar_color'   => '#12141c',
                'accent_color'    => '#6366f1',
                'secondary_color' => '#4338ca',
                'text_color'      => '#f3f4f6',
                'dark_mode'       => true,
                'border_radius'   => '1rem',
            ]);
        });
    }

    /**
     * Relación: El usuario (local) tiene su propia cola de canciones.
     */
    public function queues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function serviceTables()
    {
        // Un usuario (dueño de karaoke) tiene muchas mesas
        return $this->hasMany(ServiceTable::class);
    }
}
