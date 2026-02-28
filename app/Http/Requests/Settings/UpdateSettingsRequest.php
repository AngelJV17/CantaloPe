<?php
namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'local_name'      => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:500'], // Agregado

            // Validamos todos los colores con el mismo regex hexadecimal
            'primary_color'   => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sidebar_color'   => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'accent_color'    => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color'      => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            // Dark Mode es un booleano
            'dark_mode'       => ['required', 'boolean'],

            // Border Radius y Fuente
            'border_radius'   => ['required', 'string', 'max:20'],
            'font_family'     => ['required', 'string', 'max:50'], // Agregado

            // Datos de Negocio
            'yape_number'     => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'], // Agregado (El que te fallaba)

            // Archivos
            'logo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'favicon_path'    => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'], // Por si habilitas favicon después
        ];
    }
}
