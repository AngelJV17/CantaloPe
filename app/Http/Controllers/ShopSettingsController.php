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
        $settings = Setting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'local_name'      => $request->user()->name,
                'primary_color'   => '#090a0f',
                'sidebar_color'   => '#12141c',
                'accent_color'    => '#6366f1',
                'secondary_color' => '#4338ca',
                'text_color'      => '#f3f4f6',
                'dark_mode'       => true,
                'border_radius'   => '1rem',
                'font_family'     => 'Inter',
            ]
        );

        return Inertia::render('Settings/Edit', [
            'settings' => $settings,
            'status'   => session('status'),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            $settings = Setting::firstOrCreate(['user_id' => $request->user()->id]);

            if ($request->hasFile('logo')) {
                if ($settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }

                $file = $request->file('logo');
                // Forzamos la extensión a .png para asegurar la transparencia procesada
                $filename = 'logos/' . uniqid() . '.png';

                // --- PROCESAMIENTO MEJORADO ---
                $img = Image::read($file);

                // 1. Aplicamos Trim con tolerancia (15 es ideal para limpiar bordes sucios)
                $img->trim(tolerance: 15);

                // 2. Codificamos específicamente a PNG para mantener la transparencia pura
                $encoded = $img->toPng();

                // 3. Guardamos los datos binarios
                Storage::disk('public')->put($filename, (string) $encoded);

                $data['logo_path'] = $filename;
            }

            unset($data['logo']);

            if ($request->has('dark_mode')) {
                $data['dark_mode'] = $request->boolean('dark_mode');
            }

            $settings->update($data);
            $settings->refresh();

            return back()->with('message', '¡Imagen recortada y marca actualizada!');

        } catch (\Exception $e) {
            Log::error("Error en Settings Update: " . $e->getMessage());
            return back()->with('error', 'Error al procesar el logo: ' . $e->getMessage());
        }
    }
}
