<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { User, Mail, Lock, UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Crear Cuenta" />

        <div class="mb-10 text-center">
            <h2 class="text-3xl font-black uppercase tracking-tighter text-white italic">
                ÚNETE AL SHOW
            </h2>
            <p class="text-[10px] text-white/50 uppercase tracking-[0.3em] mt-2 font-bold">
                Crea tu cuenta de administrador
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="NOMBRE COMPLETO" class="text-white/80 text-[10px] tracking-widest mb-2" />
                <div class="relative group">
                    <TextInput id="name" type="text"
                        class="block w-full pl-11 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.name" required autofocus autocomplete="name" placeholder="Ej. Juan Pérez" />
                    <User
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="CORREO ELECTRÓNICO"
                    class="text-white/80 text-[10px] tracking-widest mb-2" />
                <div class="relative group">
                    <TextInput id="email" type="email"
                        class="block w-full pl-11 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.email" required autocomplete="username" placeholder="tu@email.com" />
                    <Mail
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="CONTRASEÑA" class="text-white/80 text-[10px] tracking-widest mb-2" />
                <div class="relative group">
                    <TextInput id="password" type="password"
                        class="block w-full pl-11 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.password" required autocomplete="new-password" placeholder="••••••••" />
                    <Lock
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="CONFIRMAR CONTRASEÑA"
                    class="text-white/80 text-[10px] tracking-widest mb-2" />
                <div class="relative group">
                    <TextInput id="password_confirmation" type="password"
                        class="block w-full pl-11 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.password_confirmation" required autocomplete="new-password"
                        placeholder="••••••••" />
                    <Lock
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-6 flex flex-col space-y-4">
                <PrimaryButton class="w-full justify-center py-5 shadow-[0_15px_30px_-10px_var(--accent)]"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <UserPlus v-if="!form.processing" class="w-5 h-5 mr-3" />
                    <span class="font-black uppercase tracking-widest italic">
                        {{ form.processing ? 'CREANDO CUENTA...' : 'REGISTRARME AHORA' }}
                    </span>
                </PrimaryButton>

                <Link :href="route('login')"
                    class="text-center text-[10px] text-white/40 hover:text-[var(--accent)] transition-all uppercase font-black tracking-[0.2em]">
                    ¿Ya tienes cuenta? Inicia sesión
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>