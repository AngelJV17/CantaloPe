<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn, Mail, Lock, Sparkles } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Iniciar Sesión" />

        <div class="mb-10 text-center relative">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 mb-4">
                <Sparkles class="w-3 h-3 text-indigo-400" />
                <span class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em]">Acceso Seguro</span>
            </div>

            <h2
                class="text-3xl font-black uppercase tracking-tighter text-white drop-shadow-[0_0_15px_rgba(99,102,241,0.3)]">
                ¡Bienvenido <span class="text-indigo-500 italic">de vuelta!</span>
            </h2>
            <p class="text-[10px] text-white/40 uppercase tracking-[0.3em] mt-3 font-bold">
                Identifícate para continuar
            </p>
        </div>

        <div v-if="status"
            class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-[11px] font-bold text-emerald-400 text-center uppercase tracking-wider">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="relative">
                <InputLabel for="email" value="CORREO ELECTRÓNICO"
                    class="text-white/60 text-[9px] font-black tracking-[0.2em] mb-2 ml-1" />
                <div class="relative group">
                    <TextInput id="email" type="email"
                        class="block w-full pl-12 bg-white/[0.03] border-white/10 text-white focus:bg-white/[0.07] focus:border-indigo-500/50 transition-all placeholder:text-white/10 py-4"
                        v-model="form.email" required autofocus autocomplete="username"
                        placeholder="admin@cantalope.com" />
                    <Mail
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-500/50 group-focus-within:text-indigo-400 group-focus-within:drop-shadow-[0_0_8px_rgba(99,102,241,0.5)] transition-all" />
                </div>
                <InputError class="mt-2 text-[10px] font-bold uppercase" :message="form.errors.email" />
            </div>

            <div class="relative">
                <div class="flex items-center justify-between mb-2 ml-1">
                    <InputLabel for="password" value="CONTRASEÑA"
                        class="text-white/60 text-[9px] font-black tracking-[0.2em]" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-[9px] uppercase font-black tracking-widest text-indigo-400/70 hover:text-indigo-300 transition-colors">
                        Recuperar acceso
                    </Link>
                </div>
                <div class="relative group">
                    <TextInput id="password" type="password"
                        class="block w-full pl-12 bg-white/[0.03] border-white/10 text-white focus:bg-white/[0.07] focus:border-indigo-500/50 transition-all placeholder:text-white/10 py-4"
                        v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />
                    <Lock
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-500/50 group-focus-within:text-indigo-400 group-focus-within:drop-shadow-[0_0_8px_rgba(99,102,241,0.5)] transition-all" />
                </div>
                <InputError class="mt-2 text-[10px] font-bold uppercase" :message="form.errors.password" />
            </div>

            <div class="flex items-center px-1">
                <label class="flex items-center group cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span
                        class="ms-3 text-[10px] font-bold uppercase tracking-[0.1em] text-white/30 group-hover:text-white/60 transition-colors">
                        Permanecer conectado
                    </span>
                </label>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-6 text-[11px] bg-indigo-600 hover:bg-indigo-500 border-none shadow-[0_10px_30px_-10px_rgba(99,102,241,0.5)] active:scale-[0.98] transition-all"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <LogIn v-if="!form.processing" class="w-4 h-4 mr-2" />
                    <span class="font-black uppercase tracking-[0.2em]">
                        {{ form.processing ? 'Verificando credenciales...' : 'Entrar a la Estación' }}
                    </span>
                </PrimaryButton>
            </div>

            <div class="pt-8 border-t border-white/5 text-center">
                <p class="text-[10px] text-white/20 uppercase tracking-[0.2em] font-bold">
                    ¿Nuevo en el sistema?
                </p>
                <Link :href="route('register')"
                    class="inline-block mt-3 text-xs font-black text-indigo-400 hover:text-indigo-300 transition-colors tracking-tighter hover:scale-105 transform">
                    CREAR UNA CUENTA NUEVA
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>