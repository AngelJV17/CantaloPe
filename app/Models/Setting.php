<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',

        // Identidad
        'local_name',
        'logo_path',
        'favicon_path',
        'description',

        // Colores y tema
        'primary_color',
        'sidebar_color',
        'accent_color',
        'secondary_color',
        'text_color',
        'theme_name',
        'theme_mode',
        'is_custom_theme',

        // Estética
        'border_radius',
        'font_family',

        // Negocio
        'yape_number',
        'whatsapp_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_custom_theme' => 'boolean',
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

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeDarkMode(Builder $query): Builder
    {
        return $query->where('theme_mode', 'dark');
    }

    public function scopeLightMode(Builder $query): Builder
    {
        return $query->where('theme_mode', 'light');
    }

    public function scopeCustomTheme(Builder $query): Builder
    {
        return $query->where('is_custom_theme', true);
    }

    public function scopePresetTheme(Builder $query): Builder
    {
        return $query->where('is_custom_theme', false);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getIsDarkModeAttribute(): bool
    {
        return $this->theme_mode === 'dark';
    }

    public function getIsLightModeAttribute(): bool
    {
        return $this->theme_mode === 'light';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        if (! $this->favicon_path) {
            return null;
        }

        return asset('storage/' . $this->favicon_path);
    }

    public function getThemeSummaryAttribute(): string
    {
        $mode = $this->is_dark_mode ? 'Oscuro' : 'Claro';

        return "{$this->theme_name} · {$mode}";
    }

    /*
    |--------------------------------------------------------------------------
    | DOMAIN HELPERS
    |--------------------------------------------------------------------------
    */

    public function useDarkMode(): bool
    {
        return $this->theme_mode === 'dark';
    }

    public function useLightMode(): bool
    {
        return $this->theme_mode === 'light';
    }

    public function markAsCustomTheme(): void
    {
        $this->update([
            'is_custom_theme' => true,
        ]);
    }

    public function markAsPresetTheme(string $themeName, string $themeMode): void
    {
        $this->update([
            'theme_name'      => $themeName,
            'theme_mode'      => $themeMode,
            'is_custom_theme' => false,
        ]);
    }

    public function applyTheme(array $theme): void
    {
        $this->update([
            'theme_name'      => $theme['theme_name'] ?? $this->theme_name,
            'theme_mode'      => $theme['theme_mode'] ?? $this->theme_mode,
            'primary_color'   => $theme['primary_color'] ?? $this->primary_color,
            'sidebar_color'   => $theme['sidebar_color'] ?? $this->sidebar_color,
            'accent_color'    => $theme['accent_color'] ?? $this->accent_color,
            'secondary_color' => $theme['secondary_color'] ?? $this->secondary_color,
            'text_color'      => $theme['text_color'] ?? $this->text_color,
            'is_custom_theme' => $theme['is_custom_theme'] ?? false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPERS
    |--------------------------------------------------------------------------
    */

    public static function defaultTheme(): array
    {
        return [
            'theme_name'      => 'Neón',
            'theme_mode'      => 'dark',
            'primary_color'   => '#090a0f',
            'sidebar_color'   => '#12141c',
            'accent_color'    => '#6366f1',
            'secondary_color' => '#4338ca',
            'text_color'      => '#f3f4f6',
            'is_custom_theme' => false,
        ];
    }
}
