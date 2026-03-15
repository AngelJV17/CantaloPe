<?php
namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;

class ShopSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $settings = Setting::firstOrCreate(
            ['user_id' => $user->id],
            [
                // Identidad
                'local_name'      => 'CANTALOPE',
                'logo_path'       => null,
                'favicon_path'    => null,
                'description'     => null,

                // Tema
                'theme_name'      => 'Neón',
                'theme_mode'      => 'dark',
                'is_custom_theme' => false,

                // Colores
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
            ]
        );

        return Inertia::render('Settings/Edit', [
            'settings' => $settings,
            'success'  => session('success'),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $settings = Setting::where('user_id', $user->id)->firstOrFail();

            if ($request->hasFile('logo')) {
                if ($settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }

                $file     = $request->file('logo');
                $filename = 'logos/' . uniqid('', true) . '.png';

                $img = Image::read($file);
                $img->trim(tolerance: 15);

                $encoded = $img->toPng();

                Storage::disk('public')->put($filename, (string) $encoded);

                $data['logo_path'] = $filename;
            }

            unset($data['logo']);

            // Normalizar theme_mode
            if (isset($data['theme_mode'])) {
                $data['theme_mode'] = in_array($data['theme_mode'], ['dark', 'light'], true)
                    ? $data['theme_mode']
                    : 'dark';
            } else {
                $data['theme_mode'] = $settings->theme_mode ?: 'dark';
            }

            // Si no llega theme_name, mantenemos el actual
            if (! isset($data['theme_name']) || blank($data['theme_name'])) {
                $data['theme_name'] = $settings->theme_name ?: 'Neón';
            }

            // Si no llega is_custom_theme, mantenemos el actual
            if (! array_key_exists('is_custom_theme', $data)) {
                $data['is_custom_theme'] = $settings->is_custom_theme ?? false;
            }

            $settings->update($data);

            return back()->with('success', '¡Identidad visual actualizada con éxito!');
        } catch (\Throwable $e) {
            Log::error('Error en Settings Update: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Hubo un problema al guardar los cambios.');
        }
    }
}
