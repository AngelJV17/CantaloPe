<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Mail, KeyRound, ArrowLeft, Loader2 } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>

        <Head title="Recuperar Acceso" />

        <div class="mb-10 flex flex-col items-center text-center">
            <div class="relative mb-6 group">
                <div
                    class="absolute inset-0 bg-indigo-500/15 rounded-full blur-xl group-hover:bg-indigo-500/25 transition-all duration-700">
                </div>
                <div class="relative p-5 bg-white/5 rounded-full border border-white/10 shadow-2xl backdrop-blur-md">
                    <KeyRound class="w-8 h-8 text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]" />
                </div>
            </div>

            <h2 class="text-2xl font-black uppercase tracking-tighter text-white mb-3">
                Restablecer <span class="text-indigo-500 italic">Acceso</span>
            </h2>

            <p class="text-[11px] text-white/40 font-bold uppercase tracking-[0.15em] leading-relaxed max-w-[280px]">
                Enviaremos un enlace de recuperación a tu bandeja de entrada.
            </p>
        </div>

        <Transition name="fade">
            <div v-if="status"
                class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-[11px] font-black text-emerald-400 text-center uppercase tracking-wider shadow-[0_0_20px_rgba(16,185,129,0.05)]">
                {{ status }}
            </div>
        </Transition>

        <form @submit.prevent="submit" class="space-y-7">
            <div class="relative">
                <InputLabel for="email" value="CORREO ELECTRÓNICO REGISTRADO"
                    class="text-white/60 text-[9px] font-black tracking-[0.2em] mb-3 ml-1" />

                <div class="relative group">
                    <TextInput id="email" type="email"
                        class="block w-full pl-12 bg-white/[0.03] border-white/10 text-white focus:bg-white/[0.07] focus:border-indigo-500/50 transition-all placeholder:text-white/10 py-4"
                        v-model="form.email" required autofocus autocomplete="username" placeholder="tu@email.com" />

                    <Mail
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-500/40 group-focus-within:text-indigo-400 transition-all" />
                </div>

                <InputError class="mt-3 text-[10px] font-bold uppercase" :message="form.errors.email" />
            </div>

            <div class="flex flex-col space-y-6 pt-2">
                <PrimaryButton
                    class="w-full justify-center py-6 text-[11px] bg-indigo-600 hover:bg-indigo-500 border-none shadow-[0_10px_30px_-10px_rgba(99,102,241,0.5)] active:scale-[0.98] transition-all uppercase"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">

                    <template v-if="form.processing">
                        <Loader2 class="animate-spin w-4 h-4 mr-2" />
                        <span>Enviando Instrucciones...</span>
                    </template>

                    <template v-else>
                        <span class="font-black tracking-[0.2em]">Enviar Enlace de Recuperación</span>
                    </template>
                </PrimaryButton>

                <Link :href="route('login')"
                    class="group flex items-center justify-center gap-2 text-[10px] text-white/30 hover:text-white transition-all uppercase font-black tracking-[0.3em]">
                    <ArrowLeft class="w-3 h-3 group-hover:-translate-x-1 transition-transform" />
                    Volver al inicio de sesión
                </Link>
            </div>
        </form>

        <div class="mt-12 text-center">
            <p class="text-[8px] text-white/10 font-black uppercase tracking-[0.5em]">Security Protocol v3.0</p>
        </div>
    </GuestLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>