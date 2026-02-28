<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// Layouts y Componentes
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Iconografía
import {
    Store, QrCode, CheckCircle, Camera, Palette,
    Type, Loader2, LayoutDashboard
} from 'lucide-vue-next';

// Utilidades
import Swal from 'sweetalert2';

// Opciones de radio igualadas a la sensación del Dashboard
const RADIUS_OPTIONS = [
    { name: 'Cuadrado', value: '0px' },
    { name: 'Sutil', value: '0.75rem' },
    { name: 'Estándar', value: '1.25rem' },
    { name: 'Full Style', value: '2rem' }, // Este es el que te gusta del Dashboard
];

const FONT_OPTIONS = [
    { name: 'Inter (Moderna)', value: 'Inter' },
    { name: 'Poppins (Geométrica)', value: 'Poppins' },
    { name: 'Montserrat (Elegante)', value: 'Montserrat' },
];

const props = defineProps({
    settings: Object,
    status: String,
});

const page = usePage();
const logoPreview = ref(null);

const form = useForm({
    local_name: props.settings?.local_name || '',
    description: props.settings?.description || '',
    logo: null,
    primary_color: props.settings?.primary_color || '#090a0f',
    sidebar_color: props.settings?.sidebar_color || '#12141c',
    accent_color: props.settings?.accent_color || '#6366f1',
    secondary_color: props.settings?.secondary_color || '#4338ca',
    text_color: props.settings?.text_color || '#f3f4f6',
    dark_mode: Boolean(props.settings?.dark_mode ?? true),
    border_radius: props.settings?.border_radius || '1.25rem',
    font_family: props.settings?.font_family || 'Inter',
    yape_number: props.settings?.yape_number || '',
    whatsapp_number: props.settings?.whatsapp_number || '',
});

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

const currentLogoUrl = computed(() => {
    if (logoPreview.value) return logoPreview.value;
    return props.settings?.logo_path ? `/storage/${props.settings.logo_path}` : null;
});

watch(() => page.props.flash, (flash) => {
    if (!flash?.message && !flash?.error) return;
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#12141c',
        color: '#ffffff',
    });
    if (flash.message) Toast.fire({ icon: 'success', title: flash.message });
    if (flash.error) Toast.fire({ icon: 'error', title: flash.error });
}, { deep: true });

const updateSettings = () => {
    if (form.processing) return;

    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('settings.update'), {
        preserveScroll: false,
        forceFormData: true,
        onSuccess: (pageResponse) => {
            form.defaults(pageResponse.props.settings);
            form.reset();
            form.logo = null;
            setTimeout(() => {
                const anchor = document.getElementById('scroll-anchor');
                if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        },
    });
};

onUnmounted(clearPreview);
</script>

<template>

    <Head title="Configuración de Marca" />

    <AuthenticatedLayout>
        <template #header>
            <span>Personalización del Karaoke</span>
        </template>

        <div id="scroll-anchor" class="relative -top-24"></div>

        <div class="max-w-6xl mx-auto pb-20 px-4" :style="{ fontFamily: form.font_family }">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="updateSettings" class="space-y-6">

                        <div class="p-8 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-2xl"
                            :style="{ borderRadius: form.border_radius }">

                            <div class="flex flex-col items-center mb-10">
                                <div class="relative group cursor-pointer">
                                    <div class="w-32 h-32 border-2 border-dashed border-white/10 flex items-center justify-center overflow-hidden bg-black/40 group-hover:border-indigo-500/50 transition-all duration-500 shadow-inner"
                                        :style="{ borderRadius: form.border_radius }">
                                        <img v-if="currentLogoUrl" :src="currentLogoUrl"
                                            class="w-full h-full object-contain p-4" />
                                        <Store v-else class="w-10 h-10 text-gray-700" />
                                        <div
                                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                            <Camera class="w-8 h-8 text-white" />
                                        </div>
                                    </div>
                                    <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        @change="handleLogoChange" accept="image/*" />
                                </div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-500/60 mt-4 text-center">
                                    Identidad Visual</p>
                            </div>

                            <div class="grid gap-6">
                                <div>
                                    <InputLabel for="local_name" value="Nombre del Establecimiento"
                                        class="text-gray-500 ml-1 uppercase text-[10px] tracking-widest font-bold" />
                                    <TextInput id="local_name" v-model="form.local_name" type="text"
                                        class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <InputLabel for="description" value="Slogan o Descripción"
                                        class="text-gray-500 ml-1 uppercase text-[10px] tracking-widest font-bold" />
                                    <textarea id="description" v-model="form.description" rows="2"
                                        class="mt-1 block w-full bg-black/20 border-white/5 text-white text-sm focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                                        :style="{ borderRadius: '0.75rem' }"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-2xl"
                            :style="{ borderRadius: form.border_radius }">
                            <div class="flex items-center mb-8">
                                <div class="p-3 bg-indigo-500/10 rounded-2xl mr-4">
                                    <Palette class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white tracking-tight">Paleta de Colores</h3>
                                    <p class="text-xs text-gray-500 font-medium">Define la atmósfera de tu interfaz.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="(label, key) in { primary_color: 'Fondo', sidebar_color: 'Menús', accent_color: 'Acento', text_color: 'Texto' }"
                                    :key="key">
                                    <InputLabel :value="label" class="text-gray-500 ml-1 text-[10px] font-bold" />
                                    <div class="mt-1 flex items-center p-2 bg-black/30 border border-white/5 hover:border-white/10 transition-colors"
                                        :style="{ borderRadius: '0.75rem' }">
                                        <input type="color" v-model="form[key]"
                                            class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer" />
                                        <span class="ml-3 font-mono text-xs text-gray-400 uppercase tracking-tighter">{{
                                            form[key] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-2xl"
                            :style="{ borderRadius: form.border_radius }">
                            <div class="flex items-center mb-8">
                                <div class="p-3 bg-pink-500/10 rounded-2xl mr-4">
                                    <Type class="w-5 h-5 text-pink-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white tracking-tight">Estética del Sistema</h3>
                                    <p class="text-xs text-gray-500 font-medium">Personaliza la forma y tipografía.</p>
                                </div>
                            </div>
                            <div class="space-y-8">
                                <div>
                                    <InputLabel value="Fuente de la Interfaz"
                                        class="text-gray-500 ml-1 text-[10px] font-bold uppercase tracking-widest mb-2" />
                                    <select v-model="form.font_family"
                                        class="mt-1 block w-full bg-[#1e2029]/50 border-white/5 rounded-xl text-white text-sm focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                        <option v-for="font in FONT_OPTIONS" :key="font.value" :value="font.value">{{
                                            font.name
                                        }}</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Nivel de Redondeado (Bordes)"
                                        class="text-gray-500 ml-1 text-[10px] font-bold uppercase tracking-widest mb-4" />
                                    <div class="flex gap-3">
                                        <button v-for="opt in RADIUS_OPTIONS" :key="opt.value" type="button"
                                            @click="form.border_radius = opt.value"
                                            :class="[form.border_radius === opt.value ? 'border-indigo-500 bg-indigo-500/10 text-white' : 'border-white/5 bg-white/5 text-gray-500 hover:bg-white/10']"
                                            class="flex-1 py-4 px-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition-all">
                                            {{ opt.name }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-2xl"
                            :style="{ borderRadius: form.border_radius }">
                            <div class="flex items-center mb-8">
                                <div class="p-3 bg-emerald-500/10 rounded-2xl mr-4">
                                    <QrCode class="w-5 h-5 text-emerald-400" />
                                </div>
                                <h3 class="text-lg font-bold text-white tracking-tight">Canales de Negocio</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="yape_number" value="Número Yape/Plin"
                                        class="text-gray-500 ml-1 text-[10px] font-bold uppercase" />
                                    <TextInput id="yape_number" v-model="form.yape_number" type="text"
                                        class="mt-1 block w-full" placeholder="900 000 000" />
                                </div>
                                <div>
                                    <InputLabel for="whatsapp_number" value="WhatsApp Pedidos"
                                        class="text-gray-500 ml-1 text-[10px] font-bold uppercase" />
                                    <TextInput id="whatsapp_number" v-model="form.whatsapp_number" type="text"
                                        class="mt-1 block w-full" placeholder="51900000000" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center pt-4">
                            <PrimaryButton :disabled="form.processing"
                                class="w-full max-w-xl justify-center py-5 text-lg shadow-2xl shadow-indigo-500/20"
                                :style="{ borderRadius: form.border_radius }">
                                <div class="flex items-center gap-3">
                                    <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                    <CheckCircle v-else class="w-5 h-5" />
                                    <span class="font-bold uppercase tracking-[0.2em] text-[11px]">{{ form.processing ?
                                        'Procesando...' : 'Aplicar Configuración' }}</span>
                                </div>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="hidden lg:block">
                    <div class="sticky top-28">
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-600 mb-4 ml-4">Vista en
                            Dispositivo
                        </p>
                        <div class="w-full aspect-[9/18.5] border-[10px] border-[#090a0f] shadow-2xl overflow-hidden relative transition-all duration-700"
                            :style="{ backgroundColor: form.primary_color, color: form.text_color, borderRadius: '3.5rem' }">

                            <div class="p-8 text-center h-full flex flex-col pt-16">
                                <div class="w-28 h-28 bg-white/5 mx-auto mb-10 border border-white/10 flex items-center justify-center overflow-hidden transition-all duration-500 shadow-2xl"
                                    :style="{ borderRadius: form.border_radius }">
                                    <img v-if="currentLogoUrl" :src="currentLogoUrl"
                                        class="w-full h-full object-contain p-4" />
                                    <span v-else
                                        class="text-[9px] text-gray-600 uppercase font-black tracking-widest">Logo</span>
                                </div>

                                <h4 class="font-bold text-2xl tracking-tighter mb-2 uppercase">
                                    {{ form.local_name || 'Nombre Local' }}
                                </h4>
                                <p class="text-[10px] opacity-50 mb-10 px-6 line-clamp-2 leading-relaxed">{{
                                    form.description ||
                                    'Slogan de tu establecimiento' }}</p>

                                <div class="space-y-4 flex-1">
                                    <div class="h-14 w-full border border-white/5 flex items-center px-4 bg-black/20"
                                        :style="{ borderRadius: form.border_radius }">
                                        <div class="h-1.5 w-1/2 bg-white/10 rounded-full"></div>
                                    </div>
                                    <div class="h-14 w-full shadow-lg flex items-center justify-center"
                                        :style="{ backgroundColor: form.accent_color, borderRadius: form.border_radius }">
                                        <div class="h-2 w-16 bg-black/20 rounded-full"></div>
                                    </div>
                                </div>
                                <p class="text-[8px] opacity-20 uppercase font-black tracking-[0.5em] mt-4">Cantalope
                                    v3.0</p>
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
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
}

.max-w-6xl {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scrollbar sutil para áreas de texto */
textarea::-webkit-scrollbar {
    width: 4px;
}

textarea::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>