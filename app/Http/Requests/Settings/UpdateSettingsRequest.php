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
            // Identidad
            'local_name'      => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:500'],

            // Tema visual
            'theme_name'      => ['nullable', 'string', 'max:100'],
            'theme_mode'      => ['nullable', 'in:dark,light'],
            'is_custom_theme' => ['nullable', 'boolean'],

            // Colores
            'primary_color'   => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sidebar_color'   => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'accent_color'    => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color'      => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            // Estética
            'border_radius'   => ['required', 'string', 'max:20'],
            'font_family'     => ['required', 'string', 'max:50'],

            // Negocio
            'yape_number'     => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],

            // Archivos
            'logo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'favicon_path'    => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
        ];
    }
}
