<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Settings, UserCircle, ShieldCheck, UserMinus } from 'lucide-vue-next';

const page = usePage();

// Recuperamos los settings para aplicar el radio de borde y la fuente global
const settings = computed(() => page.props.auth.settings);

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>

    <Head title="Ajustes de Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <span>Perfil</span>
        </template>

        <div id="scroll-anchor" class="relative -top-24"></div>

        <div class="max-w-6xl mx-auto pb-20 px-4 space-y-8" :style="{ fontFamily: settings?.font_family || 'Inter' }">

            <div class="relative overflow-hidden bg-[#12141c]/50 border border-white/5 backdrop-blur-md p-8 shadow-2xl transition-all duration-300 hover:border-white/10"
                :style="{ borderRadius: settings?.border_radius || '1.25rem' }">

                <div class="flex items-center mb-8">
                    <div class="p-3 bg-indigo-500/10 rounded-2xl mr-4">
                        <UserCircle class="w-5 h-5 text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Información Personal</h3>
                        <p class="text-xs text-gray-500 font-medium">Actualiza tu nombre y correo electrónico.</p>
                    </div>
                </div>

                <div class="relative z-10">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <div
                    class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/5 rounded-full blur-[80px] pointer-events-none">
                </div>
            </div>

            <div class="relative overflow-hidden bg-[#12141c]/50 border border-white/5 backdrop-blur-md p-8 shadow-2xl transition-all duration-300 hover:border-white/10"
                :style="{ borderRadius: settings?.border_radius || '1.25rem' }">

                <div class="flex items-center mb-8">
                    <div class="p-3 bg-pink-500/10 rounded-2xl mr-4">
                        <ShieldCheck class="w-5 h-5 text-pink-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Seguridad</h3>
                        <p class="text-xs text-gray-500 font-medium">Asegúrate de que tu cuenta use una contraseña
                            segura.</p>
                    </div>
                </div>

                <div class="relative z-10">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>

            <div class="relative overflow-hidden bg-red-500/5 border border-red-500/10 backdrop-blur-md p-8 shadow-2xl transition-all duration-300 hover:border-red-500/20"
                :style="{ borderRadius: settings?.border_radius || '1.25rem' }">

                <div class="flex items-center mb-8">
                    <div class="p-3 bg-red-500/10 rounded-2xl mr-4">
                        <UserMinus class="w-5 h-5 text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Zona de Riesgo</h3>
                        <p class="text-xs text-gray-500 font-medium">Acción permanente e irreversible.</p>
                    </div>
                </div>

                <div class="relative z-10">
                    <DeleteUserForm class="max-w-xl" />
                </div>

                <div
                    class="absolute -bottom-24 -left-24 w-64 h-64 bg-red-500/5 rounded-full blur-[80px] pointer-events-none">
                </div>
            </div>

            <div class="flex items-center justify-between px-2 pt-4 opacity-50">
                <div class="flex items-center gap-4">
                    <div class="h-[1px] w-12 bg-white/10"></div>
                    <p class="text-[9px] text-gray-600 font-bold uppercase tracking-[0.4em]">Configuración de Usuario //
                        Cantalope v3.0</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transiciones idénticas a Edit.vue y Dashboard para armonía táctil */
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
</style>