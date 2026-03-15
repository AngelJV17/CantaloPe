<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import {
    Store,
    QrCode,
    CheckCircle,
    Camera,
    Palette,
    Type,
    Loader2,
    Sparkles,
    MessageCircle,
    Wallet,
    Eye,
    LayoutTemplate,
    Wand2,
    Moon,
    Sun,
    RotateCcw,
    Check,
} from 'lucide-vue-next';

import Swal from 'sweetalert2';

/*
|--------------------------------------------------------------------------
| OPTIONS
|--------------------------------------------------------------------------
*/

const RADIUS_OPTIONS = [
    { name: 'Cuadrado', value: '0px' },
    { name: 'Sutil', value: '0.75rem' },
    { name: 'Estándar', value: '1.25rem' },
    { name: 'Full Style', value: '2rem' },
];

const FONT_OPTIONS = [
    { name: 'Inter', value: 'Inter', desc: 'Moderna y limpia' },
    { name: 'Poppins', value: 'Poppins', desc: 'Comercial y amigable' },
    { name: 'Montserrat', value: 'Montserrat', desc: 'Elegante y fuerte' },
    { name: 'Oswald', value: 'Oswald', desc: 'Ideal para show' },
    { name: 'Bebas Neue', value: 'Bebas Neue', desc: 'Impacto en títulos' },
    { name: 'Rajdhani', value: 'Rajdhani', desc: 'Futurista / tech' },
    { name: 'Nunito', value: 'Nunito', desc: 'Suave y cercana' },
];

const DARK_THEME_PRESETS = [
    {
        name: 'Neón',
        mode: 'dark',
        description: 'Clásico moderno',
        primary_color: '#090a0f',
        sidebar_color: '#12141c',
        accent_color: '#6366f1',
        secondary_color: '#4338ca',
        text_color: '#f3f4f6',
    },
    {
        name: 'Fuego',
        mode: 'dark',
        description: 'Escenario intenso',
        primary_color: '#0b0b0b',
        sidebar_color: '#1a0c0c',
        accent_color: '#ef4444',
        secondary_color: '#b91c1c',
        text_color: '#f3f4f6',
    },
    {
        name: 'Tropical',
        mode: 'dark',
        description: 'Alegre y fresco',
        primary_color: '#0c1115',
        sidebar_color: '#132028',
        accent_color: '#22c55e',
        secondary_color: '#15803d',
        text_color: '#f3f4f6',
    },
    {
        name: 'Morado Show',
        mode: 'dark',
        description: 'Nocturno premium',
        primary_color: '#0a0914',
        sidebar_color: '#1a1633',
        accent_color: '#a855f7',
        secondary_color: '#7e22ce',
        text_color: '#f3f4f6',
    },
    {
        name: 'Azul Eléctrico',
        mode: 'dark',
        description: 'Tech y elegante',
        primary_color: '#050b14',
        sidebar_color: '#0f1a2a',
        accent_color: '#3b82f6',
        secondary_color: '#1d4ed8',
        text_color: '#f3f4f6',
    },
    {
        name: 'Oro Premium',
        mode: 'dark',
        description: 'Lounge / VIP',
        primary_color: '#0b0b0b',
        sidebar_color: '#1a1a1a',
        accent_color: '#facc15',
        secondary_color: '#ca8a04',
        text_color: '#f3f4f6',
    }
];

const LIGHT_THEME_PRESETS = [
    {
        name: 'Claro Minimal',
        mode: 'light',
        description: 'Limpio y moderno',
        primary_color: '#f8fafc',
        sidebar_color: '#ffffff',
        accent_color: '#6366f1',
        secondary_color: '#4338ca',
        text_color: '#0f172a',
    },
    {
        name: 'Arena',
        mode: 'light',
        description: 'Cálido y elegante',
        primary_color: '#fffaf0',
        sidebar_color: '#ffffff',
        accent_color: '#f59e0b',
        secondary_color: '#d97706',
        text_color: '#1f2937',
    },
    {
        name: 'Mint',
        mode: 'light',
        description: 'Fresco y actual',
        primary_color: '#f0fdf4',
        sidebar_color: '#ffffff',
        accent_color: '#10b981',
        secondary_color: '#059669',
        text_color: '#1f2937',
    },
    {
        name: 'Sky',
        mode: 'light',
        description: 'Suave y brillante',
        primary_color: '#eff6ff',
        sidebar_color: '#ffffff',
        accent_color: '#3b82f6',
        secondary_color: '#2563eb',
        text_color: '#0f172a',
    },
];

const DEFAULT_THEME = {
    name: 'Neón',
    mode: 'dark',
    primary_color: '#090a0f',
    sidebar_color: '#12141c',
    accent_color: '#6366f1',
    secondary_color: '#4338ca',
    text_color: '#f3f4f6',
};

/*
|--------------------------------------------------------------------------
| PAGE / PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps({
    settings: Object,
    success: String,
});

const page = usePage();
const logoPreview = ref(null);

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = useForm({
    local_name: props.settings?.local_name || '',
    description: props.settings?.description || '',
    logo: null,

    primary_color: props.settings?.primary_color || DEFAULT_THEME.primary_color,
    sidebar_color: props.settings?.sidebar_color || DEFAULT_THEME.sidebar_color,
    accent_color: props.settings?.accent_color || DEFAULT_THEME.accent_color,
    secondary_color: props.settings?.secondary_color || DEFAULT_THEME.secondary_color,
    text_color: props.settings?.text_color || DEFAULT_THEME.text_color,

    theme_name: props.settings?.theme_name || DEFAULT_THEME.name,
    theme_mode: props.settings?.theme_mode || DEFAULT_THEME.mode,
    is_custom_theme: Boolean(props.settings?.is_custom_theme ?? false),

    border_radius: props.settings?.border_radius || '1.25rem',
    font_family: props.settings?.font_family || 'Inter',

    yape_number: props.settings?.yape_number || '',
    whatsapp_number: props.settings?.whatsapp_number || '',
});

/*
|--------------------------------------------------------------------------
| COMPUTED
|--------------------------------------------------------------------------
*/

const isLightMode = computed(() => form.theme_mode === 'light');
const isDarkMode = computed(() => form.theme_mode === 'dark');

const presetsByMode = computed(() => {
    return isLightMode.value ? LIGHT_THEME_PRESETS : DARK_THEME_PRESETS;
});

const currentLogoUrl = computed(() => {
    if (logoPreview.value) return logoPreview.value;
    return props.settings?.logo_path ? `/storage/${props.settings.logo_path}` : null;
});

const currentPresetName = computed(() => {
    const match = presetsByMode.value.find((preset) =>
        preset.primary_color === form.primary_color &&
        preset.sidebar_color === form.sidebar_color &&
        preset.accent_color === form.accent_color &&
        preset.secondary_color === form.secondary_color &&
        preset.text_color === form.text_color
    );

    return match?.name || 'Personalizado';
});

/*
|--------------------------------------------------------------------------
| UI TOKENS
|--------------------------------------------------------------------------
*/

const uiTokens = computed(() => {
    if (isLightMode.value) {
        return {
            pageBg: '#f3f6fb',
            heroBg: 'rgba(255,255,255,0.92)',
            cardBg: 'rgba(255,255,255,0.88)',
            cardSoftBg: 'rgba(15,23,42,0.03)',
            blockBg: 'rgba(15,23,42,0.04)',
            stickyBg: 'rgba(255,255,255,0.94)',
            border: 'rgba(15,23,42,0.08)',
            borderStrong: 'rgba(15,23,42,0.12)',
            textPrimary: '#0f172a',
            textSecondary: '#475569',
            textMuted: '#64748b',
            textSoft: '#94a3b8',
            inputBg: 'rgba(255,255,255,0.96)',
            inputBorder: 'rgba(15,23,42,0.10)',
            optionBg: 'rgba(255,255,255,0.92)',
            optionBgActive: 'rgba(99,102,241,0.08)',
            shadow: '0 20px 60px rgba(15,23,42,0.10)',
            blur: 'blur(18px)',
        };
    }

    return {
        pageBg: '#090a0f',
        heroBg: '#0f1117',
        cardBg: '#12141c',
        cardSoftBg: 'rgba(255,255,255,0.02)',
        blockBg: '#0c0d12',
        stickyBg: '#0b0d12',
        border: 'rgba(255,255,255,0.06)',
        borderStrong: 'rgba(255,255,255,0.12)',
        textPrimary: '#ffffff',
        textSecondary: '#9ca3af',
        textMuted: '#6b7280',
        textSoft: '#4b5563',
        inputBg: '#0f1117',
        inputBorder: 'rgba(255,255,255,0.06)',
        optionBg: '#10131a',
        optionBgActive: 'rgba(99,102,241,0.10)',
        shadow: '0 20px 60px rgba(0,0,0,0.40)',
        blur: 'none',
    };
});

const pageShellStyle = computed(() => ({
    fontFamily: form.font_family,
    backgroundColor: uiTokens.value.pageBg,
    borderRadius: form.border_radius,
}));

const heroCardStyle = computed(() => ({
    backgroundColor: uiTokens.value.heroBg,
    borderColor: uiTokens.value.border,
    borderRadius: form.border_radius,
    color: uiTokens.value.textPrimary,
    boxShadow: uiTokens.value.shadow,
    backdropFilter: uiTokens.value.blur,
}));

const sectionCardStyle = computed(() => ({
    backgroundColor: uiTokens.value.cardBg,
    borderColor: uiTokens.value.border,
    borderRadius: form.border_radius,
    color: uiTokens.value.textPrimary,
    boxShadow: uiTokens.value.shadow,
    backdropFilter: uiTokens.value.blur,
}));

const stickyBarStyle = computed(() => ({
    backgroundColor: uiTokens.value.stickyBg,
    borderColor: uiTokens.value.borderStrong,
    borderRadius: form.border_radius,
    color: uiTokens.value.textPrimary,
    boxShadow: uiTokens.value.shadow,
    backdropFilter: uiTokens.value.blur,
}));

const subtleBlockStyle = computed(() => ({
    backgroundColor: uiTokens.value.blockBg,
    borderColor: uiTokens.value.border,
    borderRadius: '1rem',
}));

const previewSurfaceStyle = computed(() => ({
    backgroundColor: form.primary_color,
    color: form.text_color,
    fontFamily: form.font_family,
}));

const previewCardStyle = computed(() => ({
    backgroundColor: form.sidebar_color,
    borderRadius: form.border_radius,
}));

const previewButtonStyle = computed(() => ({
    backgroundColor: form.accent_color,
    borderRadius: form.border_radius,
}));

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const clearPreview = () => {
    if (logoPreview.value?.startsWith('blob:')) {
        URL.revokeObjectURL(logoPreview.value);
    }
};

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        clearPreview();
        logoPreview.value = URL.createObjectURL(file);
    }
};

const applyPreset = (preset) => {
    form.theme_name = preset.name;
    form.theme_mode = preset.mode;
    form.primary_color = preset.primary_color;
    form.sidebar_color = preset.sidebar_color;
    form.accent_color = preset.accent_color;
    form.secondary_color = preset.secondary_color;
    form.text_color = preset.text_color;
    form.is_custom_theme = false;
};

const switchThemeMode = (mode) => {
    if (form.theme_mode === mode) return;

    form.theme_mode = mode;

    const firstPreset = mode === 'light' ? LIGHT_THEME_PRESETS[0] : DARK_THEME_PRESETS[0];
    applyPreset(firstPreset);
};

const restoreDefaultTheme = () => {
    applyPreset(DEFAULT_THEME);
};

const markThemeAsCustom = () => {
    form.theme_name = 'Personalizado';
    form.is_custom_theme = true;
};

const updateSettings = () => {
    if (form.processing) return;

    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('settings.update'), {
        preserveScroll: false,
        forceFormData: true,
    });
};

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/

watch(
    () => form.accent_color,
    (newVal, oldVal) => {
        if (oldVal !== undefined && newVal !== oldVal && currentPresetName.value !== 'Personalizado') {
            markThemeAsCustom();
        }
    }
);

watch(
    () => [form.primary_color, form.sidebar_color, form.secondary_color, form.text_color],
    (newVal, oldVal) => {
        if (!oldVal) return;

        const changed = JSON.stringify(newVal) !== JSON.stringify(oldVal);
        if (changed && currentPresetName.value === 'Personalizado') {
            form.is_custom_theme = true;
        }
    }
);

/*
|--------------------------------------------------------------------------
| TOASTS
|--------------------------------------------------------------------------
*/

watch(() => page.props.flash, (flash) => {
    if (!flash) return;

    const message = flash.success || flash.message;
    const error = flash.error;

    if (!message && !error) return;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#12141c',
        color: '#ffffff',
        customClass: {
            popup: 'border border-white/10 backdrop-blur-xl rounded-2xl shadow-2xl',
        }
    });

    if (message) {
        Toast.fire({
            icon: 'success',
            title: String(message).toUpperCase(),
            iconColor: form.accent_color || '#6366f1'
        });
    }

    if (error) {
        Toast.fire({
            icon: 'error',
            title: String(error).toUpperCase()
        });
    }
}, { deep: true, immediate: true });

onUnmounted(clearPreview);
</script>

<template>

    <Head title="Configuración de Marca" />

    <AuthenticatedLayout>
        <template #header>
            <span>Personalización del Karaoke</span>
        </template>

        <div class="max-w-7xl mx-auto pb-24 px-4 mt-8 transition-all duration-300" :style="{
            ...pageShellStyle,
            minHeight: '100vh'
        }">
            <div class="grid grid-cols-1 xl:grid-cols-[1.35fr_0.85fr] gap-8">

                <div class="space-y-6">
                    <div class="relative overflow-hidden border p-8" :style="heroCardStyle">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-white/[0.03] to-transparent pointer-events-none">
                        </div>
                        <div class="absolute -top-20 -right-20 w-48 h-48 rounded-full blur-3xl opacity-20"
                            :style="{ backgroundColor: form.accent_color }"></div>

                        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="max-w-2xl">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-full mb-4"
                                    :style="subtleBlockStyle">
                                    <Sparkles class="w-4 h-4" :style="{ color: form.accent_color }" />
                                    <span class="text-[10px] font-black uppercase tracking-[0.25em]"
                                        :style="{ color: uiTokens.textSecondary }">
                                        Personalización PRO
                                    </span>
                                </div>

                                <h2 class="text-3xl font-black uppercase tracking-tight italic"
                                    :style="{ color: uiTokens.textPrimary }">
                                    Diseña la identidad de tu karaoke
                                </h2>

                                <p class="text-sm mt-3 max-w-xl leading-relaxed"
                                    :style="{ color: uiTokens.textSecondary }">
                                    Configura la marca, el estilo visual y los canales de negocio con una experiencia
                                    simple, rápida y lista para producción.
                                </p>
                            </div>

                            <div class="shrink-0 px-4 py-3 border text-center rounded-2xl" :style="subtleBlockStyle">
                                <p class="text-[10px] font-black uppercase tracking-[0.25em]"
                                    :style="{ color: uiTokens.textMuted }">
                                    Tema actual
                                </p>
                                <p class="text-sm font-black uppercase mt-2" :style="{ color: form.accent_color }">
                                    {{ currentPresetName }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="updateSettings" class="space-y-6">
                        <section class="border p-8" :style="sectionCardStyle">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="p-3 rounded-2xl bg-indigo-500/10">
                                    <Store class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black tracking-tight"
                                        :style="{ color: uiTokens.textPrimary }">
                                        Identidad del local
                                    </h3>
                                    <p class="text-xs font-medium" :style="{ color: uiTokens.textSecondary }">
                                        Logo, nombre y presentación del karaoke.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-[180px_1fr] gap-8 items-start">
                                <div class="flex flex-col items-center">
                                    <div class="relative group cursor-pointer">
                                        <div class="w-40 h-40 border-2 border-dashed flex items-center justify-center overflow-hidden group-hover:border-indigo-500/50 transition-all duration-500 shadow-inner"
                                            :style="{
                                                borderRadius: form.border_radius,
                                                borderColor: uiTokens.borderStrong,
                                                backgroundColor: uiTokens.blockBg
                                            }">
                                            <img v-if="currentLogoUrl" :src="currentLogoUrl"
                                                class="w-full h-full object-contain p-4" />
                                            <Store v-else class="w-12 h-12" :style="{ color: uiTokens.textSoft }" />
                                            <div
                                                class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <Camera class="w-8 h-8 text-white" />
                                            </div>
                                        </div>

                                        <input type="file"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                            @change="handleLogoChange" accept="image/*" />
                                    </div>

                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] mt-4 text-center"
                                        :style="{ color: uiTokens.textMuted }">
                                        Logo del karaoke
                                    </p>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <InputLabel for="local_name" value="Nombre del establecimiento"
                                            class="ml-1 uppercase text-[10px] tracking-widest font-bold"
                                            :style="{ color: uiTokens.textMuted }" />
                                        <TextInput id="local_name" v-model="form.local_name" type="text"
                                            class="mt-1 block w-full" />
                                    </div>

                                    <div>
                                        <InputLabel for="description" value="Descripción / slogan"
                                            class="ml-1 uppercase text-[10px] tracking-widest font-bold"
                                            :style="{ color: uiTokens.textMuted }" />
                                        <textarea id="description" v-model="form.description" rows="3"
                                            class="mt-1 block w-full text-sm transition-all" :style="{
                                                borderRadius: '0.75rem',
                                                backgroundColor: uiTokens.inputBg,
                                                borderColor: uiTokens.inputBorder,
                                                color: uiTokens.textPrimary
                                            }"></textarea>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="border p-8" :style="sectionCardStyle">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="p-3 rounded-2xl bg-amber-500/10">
                                    <Palette class="w-5 h-5 text-amber-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black tracking-tight"
                                        :style="{ color: uiTokens.textPrimary }">
                                        Modo visual
                                    </h3>
                                    <p class="text-xs font-medium" :style="{ color: uiTokens.textSecondary }">
                                        Selecciona la base visual de la interfaz.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="switchThemeMode('dark')"
                                    class="p-5 border transition-all text-left" :style="{
                                        borderRadius: '1rem',
                                        borderColor: isDarkMode ? uiTokens.borderStrong : uiTokens.border,
                                        backgroundColor: isDarkMode ? uiTokens.optionBgActive : uiTokens.optionBg
                                    }">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black uppercase"
                                                :style="{ color: uiTokens.textPrimary }">
                                                Modo oscuro
                                            </p>
                                            <p class="text-xs mt-1" :style="{ color: uiTokens.textSecondary }">
                                                Ideal para show nocturno
                                            </p>
                                        </div>
                                        <Moon class="w-5 h-5 text-indigo-400" />
                                    </div>
                                </button>

                                <button type="button" @click="switchThemeMode('light')"
                                    class="p-5 border transition-all text-left" :style="{
                                        borderRadius: '1rem',
                                        borderColor: isLightMode ? uiTokens.borderStrong : uiTokens.border,
                                        backgroundColor: isLightMode ? uiTokens.optionBgActive : uiTokens.optionBg
                                    }">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black uppercase"
                                                :style="{ color: uiTokens.textPrimary }">
                                                Modo claro
                                            </p>
                                            <p class="text-xs mt-1" :style="{ color: uiTokens.textSecondary }">
                                                Más limpio y brillante
                                            </p>
                                        </div>
                                        <Sun class="w-5 h-5 text-yellow-400" />
                                    </div>
                                </button>
                            </div>
                        </section>

                        <section class="border p-8" :style="sectionCardStyle">
                            <div class="flex items-center justify-between gap-4 mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-2xl bg-fuchsia-500/10">
                                        <LayoutTemplate class="w-5 h-5 text-fuchsia-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black tracking-tight"
                                            :style="{ color: uiTokens.textPrimary }">
                                            Temas visuales
                                        </h3>
                                        <p class="text-xs font-medium" :style="{ color: uiTokens.textSecondary }">
                                            Escoge un preset listo para usar.
                                        </p>
                                    </div>
                                </div>

                                <button type="button" @click="restoreDefaultTheme"
                                    class="inline-flex items-center gap-2 px-4 py-2 border transition text-[10px] font-black uppercase tracking-[0.2em]"
                                    :style="{
                                        borderRadius: '0.85rem',
                                        borderColor: uiTokens.borderStrong,
                                        backgroundColor: uiTokens.cardSoftBg,
                                        color: uiTokens.textPrimary
                                    }">
                                    <RotateCcw class="w-3.5 h-3.5" />
                                    Restaurar por defecto
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                <button v-for="preset in presetsByMode" :key="`${preset.mode}-${preset.name}`"
                                    type="button" @click="applyPreset(preset)"
                                    class="group border text-left p-4 transition-all hover:scale-[1.01]" :style="{
                                        borderRadius: '1rem',
                                        borderColor: currentPresetName === preset.name ? uiTokens.borderStrong : uiTokens.border,
                                        backgroundColor: uiTokens.optionBg
                                    }">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="text-sm font-black uppercase tracking-tight"
                                                :style="{ color: uiTokens.textPrimary }">
                                                {{ preset.name }}
                                            </p>
                                            <p class="text-[10px] uppercase font-bold tracking-[0.2em] mt-1"
                                                :style="{ color: uiTokens.textMuted }">
                                                {{ preset.description }}
                                            </p>
                                        </div>

                                        <Check v-if="currentPresetName === preset.name" class="w-4 h-4"
                                            :style="{ color: form.accent_color }" />
                                        <Wand2 v-else class="w-4 h-4 transition"
                                            :style="{ color: uiTokens.textSoft }" />
                                    </div>

                                    <div class="h-24 overflow-hidden border"
                                        :style="{ borderRadius: '0.9rem', backgroundColor: preset.primary_color, borderColor: 'rgba(255,255,255,0.05)' }">
                                        <div class="h-3 w-full" :style="{ backgroundColor: preset.accent_color }"></div>
                                        <div class="grid grid-cols-[1fr_2fr] h-[calc(100%-12px)]">
                                            <div :style="{ backgroundColor: preset.sidebar_color }"></div>
                                            <div class="p-3 flex flex-col justify-between">
                                                <div class="space-y-2">
                                                    <div class="h-2 rounded-full w-20"
                                                        :style="{ backgroundColor: `${preset.text_color}30` }"></div>
                                                    <div class="h-2 rounded-full w-14"
                                                        :style="{ backgroundColor: `${preset.text_color}20` }"></div>
                                                </div>
                                                <div class="h-6 rounded-lg w-20"
                                                    :style="{ backgroundColor: preset.accent_color }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </section>

                        <section class="border p-8" :style="sectionCardStyle">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="p-3 rounded-2xl bg-pink-500/10">
                                    <Type class="w-5 h-5 text-pink-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black tracking-tight"
                                        :style="{ color: uiTokens.textPrimary }">
                                        Estilo del sistema
                                    </h3>
                                    <p class="text-xs font-medium" :style="{ color: uiTokens.textSecondary }">
                                        Tipografía, bordes y color de marca.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-8">
                                <div>
                                    <p class="ml-1 text-[10px] font-bold uppercase tracking-widest mb-4"
                                        :style="{ color: uiTokens.textMuted }">
                                        Fuente de la interfaz
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <button v-for="font in FONT_OPTIONS" :key="font.value" type="button"
                                            @click="form.font_family = font.value"
                                            class="text-left border p-4 transition-all" :style="{
                                                borderRadius: '1rem',
                                                fontFamily: font.value,
                                                borderColor: form.font_family === font.value ? uiTokens.borderStrong : uiTokens.border,
                                                backgroundColor: form.font_family === font.value ? uiTokens.optionBgActive : uiTokens.optionBg
                                            }">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xl font-black tracking-tight"
                                                        :style="{ color: uiTokens.textPrimary }">
                                                        {{ font.name }}
                                                    </p>
                                                    <p class="text-xs mt-1 font-medium"
                                                        :style="{ color: uiTokens.textSecondary }">
                                                        {{ font.desc }}
                                                    </p>
                                                </div>

                                                <div v-if="form.font_family === font.value"
                                                    class="w-2.5 h-2.5 rounded-full mt-2"
                                                    :style="{ backgroundColor: form.accent_color }"></div>
                                            </div>

                                            <p class="mt-4 text-sm" :style="{ color: uiTokens.textSecondary }">
                                                El escenario está listo
                                            </p>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <p class="ml-1 text-[10px] font-bold uppercase tracking-widest mb-4"
                                        :style="{ color: uiTokens.textMuted }">
                                        Bordes de la interfaz
                                    </p>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <button v-for="opt in RADIUS_OPTIONS" :key="opt.value" type="button"
                                            @click="form.border_radius = opt.value" class="border p-4 transition-all"
                                            :style="{
                                                borderRadius: '1rem',
                                                borderColor: form.border_radius === opt.value ? uiTokens.borderStrong : uiTokens.border,
                                                backgroundColor: form.border_radius === opt.value ? uiTokens.optionBgActive : uiTokens.optionBg
                                            }">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-16 h-12 border" :style="{
                                                    borderRadius: opt.value,
                                                    borderColor: uiTokens.borderStrong,
                                                    backgroundColor: uiTokens.cardSoftBg
                                                }"></div>
                                                <span class="text-[10px] font-black uppercase tracking-[0.2em]"
                                                    :style="{ color: uiTokens.textPrimary }">
                                                    {{ opt.name }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <p class="ml-1 text-[10px] font-bold uppercase tracking-widest mb-3"
                                        :style="{ color: uiTokens.textMuted }">
                                        Color principal de la marca
                                    </p>

                                    <div class="flex items-center gap-4 p-3 border" :style="subtleBlockStyle">
                                        <input type="color" v-model="form.accent_color" @input="markThemeAsCustom"
                                            class="w-14 h-14 rounded-xl bg-transparent border-none cursor-pointer" />

                                        <div class="flex-1">
                                            <p class="text-sm font-black uppercase tracking-tight"
                                                :style="{ color: uiTokens.textPrimary }">
                                                Color de botones y resaltados
                                            </p>
                                            <p class="text-[11px] mt-1" :style="{ color: uiTokens.textSecondary }">
                                                Este color se usará para botones principales, indicadores y acentos.
                                            </p>
                                        </div>

                                        <div class="px-3 py-2 text-xs font-mono uppercase border rounded-xl" :style="{
                                            borderColor: uiTokens.borderStrong,
                                            backgroundColor: uiTokens.cardSoftBg,
                                            color: uiTokens.textPrimary
                                        }">
                                            {{ form.accent_color }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="border p-8" :style="sectionCardStyle">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="p-3 rounded-2xl bg-emerald-500/10">
                                    <QrCode class="w-5 h-5 text-emerald-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black tracking-tight"
                                        :style="{ color: uiTokens.textPrimary }">
                                        Canales de negocio
                                    </h3>
                                    <p class="text-xs font-medium" :style="{ color: uiTokens.textSecondary }">
                                        Datos de pago y contacto del local.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="yape_number" value="Número Yape / Plin"
                                        class="ml-1 text-[10px] font-bold uppercase tracking-widest"
                                        :style="{ color: uiTokens.textMuted }" />
                                    <div class="relative mt-1">
                                        <Wallet class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4"
                                            :style="{ color: uiTokens.textSoft }" />
                                        <TextInput id="yape_number" v-model="form.yape_number" type="text"
                                            class="block w-full pl-11" placeholder="900 000 000" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="whatsapp_number" value="WhatsApp pedidos"
                                        class="ml-1 text-[10px] font-bold uppercase tracking-widest"
                                        :style="{ color: uiTokens.textMuted }" />
                                    <div class="relative mt-1">
                                        <MessageCircle class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4"
                                            :style="{ color: uiTokens.textSoft }" />
                                        <TextInput id="whatsapp_number" v-model="form.whatsapp_number" type="text"
                                            class="block w-full pl-11" placeholder="51900000000" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="sticky bottom-4 z-20 border p-4" :style="stickyBarStyle">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                <div class="text-center md:text-left">
                                    <p class="text-xs font-black uppercase tracking-[0.2em]"
                                        :style="{ color: uiTokens.textPrimary }">
                                        Listo para aplicar cambios
                                    </p>
                                    <p class="text-[11px] mt-1" :style="{ color: uiTokens.textSecondary }">
                                        Guarda la identidad visual y comercial de tu karaoke.
                                    </p>
                                </div>

                                <PrimaryButton :disabled="form.processing"
                                    class="w-full md:w-auto justify-center py-4 px-8 shadow-2xl"
                                    :style="{ borderRadius: form.border_radius }">
                                    <div class="flex items-center gap-3">
                                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                        <CheckCircle v-else class="w-5 h-5" />
                                        <span class="font-bold uppercase tracking-[0.2em] text-[11px]">
                                            {{ form.processing ? 'Procesando...' : 'Aplicar configuración' }}
                                        </span>
                                    </div>
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="xl:sticky xl:top-28 h-fit space-y-6">
                    <div class="border p-6" :style="sectionCardStyle">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="p-2 rounded-xl bg-sky-500/10">
                                <Eye class="w-4 h-4 text-sky-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-[0.15em]"
                                    :style="{ color: uiTokens.textPrimary }">
                                    Vista previa
                                </h3>
                                <p class="text-[11px]" :style="{ color: uiTokens.textSecondary }">
                                    Así se verá para tus clientes
                                </p>
                            </div>
                        </div>

                        <div class="w-full aspect-[9/18.5] border-[10px] border-[#090a0f] shadow-2xl overflow-hidden relative transition-all duration-700"
                            :style="{ ...previewSurfaceStyle, borderRadius: '3rem' }">
                            <div class="h-2 w-full" :style="{ backgroundColor: form.accent_color }"></div>

                            <div class="p-6 text-center h-full flex flex-col">
                                <div class="w-24 h-24 bg-white/5 mx-auto mb-6 border border-white/10 flex items-center justify-center overflow-hidden shadow-2xl"
                                    :style="{ borderRadius: form.border_radius }">
                                    <img v-if="currentLogoUrl" :src="currentLogoUrl"
                                        class="w-full h-full object-contain p-3" />
                                    <span v-else class="text-[9px] uppercase font-black tracking-widest"
                                        :style="{ color: form.theme_mode === 'light' ? '#94a3b8' : '#6b7280' }">
                                        Logo
                                    </span>
                                </div>

                                <h4 class="font-black text-xl tracking-tight uppercase">
                                    {{ form.local_name || 'Nombre del local' }}
                                </h4>

                                <p class="text-[10px] opacity-60 mt-2 px-3 line-clamp-2 leading-relaxed">
                                    {{ form.description || 'Donde nacen las estrellas' }}
                                </p>

                                <div class="mt-6 space-y-4">
                                    <div class="h-12 w-full border border-white/5 flex items-center px-4"
                                        :style="previewCardStyle">
                                        <div class="h-1.5 w-1/2 bg-white/10 rounded-full"></div>
                                    </div>

                                    <div class="h-12 w-full shadow-lg flex items-center justify-center text-white font-black uppercase tracking-[0.2em] text-[10px]"
                                        :style="previewButtonStyle">
                                        Pedir canción
                                    </div>

                                    <div class="p-4 border border-white/5 text-left" :style="previewCardStyle">
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em]"
                                            :style="{ color: form.theme_mode === 'light' ? '#64748b' : '#9ca3af' }">
                                            En cola
                                        </p>
                                        <p class="mt-2 text-sm font-black uppercase">Mesa 12</p>
                                        <p class="text-xs opacity-70 font-bold mt-1">Ana - Shakira</p>
                                    </div>
                                </div>

                                <div class="mt-auto pt-6">
                                    <p class="text-[8px] opacity-20 uppercase font-black tracking-[0.5em]">
                                        Cantalope v3.0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border p-5" :style="sectionCardStyle">
                        <p class="text-[10px] font-black uppercase tracking-[0.25em] mb-4"
                            :style="{ color: uiTokens.textMuted }">
                            Resumen actual
                        </p>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Tema</span>
                                <span class="font-black uppercase" :style="{ color: uiTokens.textPrimary }">{{
                                    currentPresetName
                                    }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Modo</span>
                                <span class="font-black uppercase" :style="{ color: uiTokens.textPrimary }">{{
                                    form.theme_mode
                                    }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Fuente</span>
                                <span class="font-black" :style="{ color: uiTokens.textPrimary }">{{ form.font_family
                                    }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Bordes</span>
                                <span class="font-black" :style="{ color: uiTokens.textPrimary }">{{ form.border_radius
                                    }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Estado</span>
                                <span class="font-black uppercase" :style="{ color: uiTokens.textPrimary }">
                                    {{ form.is_custom_theme ? 'Personalizado' : 'Preset' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :style="{ color: uiTokens.textSecondary }">Acento</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full border"
                                        :style="{ backgroundColor: form.accent_color, borderColor: uiTokens.borderStrong }">
                                    </div>
                                    <span class="font-mono text-xs" :style="{ color: uiTokens.textPrimary }">{{
                                        form.accent_color }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
* {
    transition-property: background-color, border-color, color, transform, opacity, box-shadow;
    transition-duration: 300ms;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.max-w-7xl {
    animation: fadeIn 0.7s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

textarea::-webkit-scrollbar {
    width: 4px;
}

textarea::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.12);
    border-radius: 10px;
}
</style>