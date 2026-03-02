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
        // IMPORTANTE: Aseguramos que busque los ajustes DEL USUARIO logueado
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
            // Cambiamos 'status' por session('success') para ser consistentes con SweetAlert
            'success'  => session('success'), 
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Buscamos los ajustes específicos de este dueño
            $settings = Setting::where('user_id', $user->id)->firstOrFail();

            if ($request->hasFile('logo')) {
                // Limpieza del logo anterior
                if ($settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }

                $file     = $request->file('logo');
                $filename = 'logos/' . uniqid() . '.png';

                // PROCESAMIENTO CON INTERVENTION IMAGE v3
                $img = Image::read($file);

                // Trim para eliminar bordes vacíos y asegurar transparencia PNG
                $img->trim(tolerance: 15);
                $encoded = $img->toPng();

                Storage::disk('public')->put($filename, (string) $encoded);
                $data['logo_path'] = $filename;
            }

            unset($data['logo']);

            if ($request->has('dark_mode')) {
                $data['dark_mode'] = $request->boolean('dark_mode');
            }

            $settings->update($data);

            // --- EL CAMBIO CLAVE AQUÍ ---
            // Usamos 'success' en lugar de 'message' para que Vue lo detecte correctamente
            return back()->with('success', '¡Identidad visual actualizada con éxito!');

        } catch (\Exception $e) {
            Log::error("Error en Settings Update: " . $e->getMessage());
            return back()->with('error', 'Hubo un problema al guardar los cambios: ' . $e->getMessage());
        }
    }
}
