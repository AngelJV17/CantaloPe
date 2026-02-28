<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Send, MailCheck, LogOut } from 'lucide-vue-next'; // Iconos para dar vida

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>

        <Head title="Verificar Email" />

        <div class="mb-10 flex flex-col items-center text-center">
            <div
                class="mb-5 p-4 bg-[var(--accent)]/10 rounded-full border border-[var(--accent)]/30 shadow-[0_0_20px_var(--accent)]/20">
                <MailCheck class="w-10 h-10 text-[var(--accent)] drop-shadow-[0_0_8px_var(--accent)]" />
            </div>

            <h2 class="text-2xl font-black uppercase tracking-widest text-white italic mb-3">
                CASI LISTO
            </h2>

            <p class="text-sm text-white/60 font-medium leading-relaxed max-w-[320px]">
                ¡Gracias por unirte! Antes de empezar, verifica tu correo haciendo clic en el enlace que te enviamos.
                ¿No llegó? Te enviamos otro.
            </p>
        </div>

        <div v-if="verificationLinkSent"
            class="mb-8 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-xs font-black text-green-400 text-center animate-pulse">
            UN NUEVO ENLACE HA SIDO ENVIADO A TU CORREO
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-6">
                <PrimaryButton class="w-full justify-center py-5 shadow-[0_10px_30px_-10px_var(--accent)]"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <Send class="w-4 h-4 mr-3" />
                    <span class="font-black uppercase tracking-widest italic">
                        {{ form.processing ? 'REENVIANDO...' : 'REENVIAR VERIFICACIÓN' }}
                    </span>
                </PrimaryButton>

                <div class="flex justify-center">
                    <Link :href="route('logout')" method="post" as="button"
                        class="text-[10px] font-black uppercase tracking-[0.3em] text-white/30 hover:text-red-400 transition-colors flex items-center">
                        <LogOut class="w-3 h-3 mr-2" />
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>