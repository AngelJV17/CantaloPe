<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Key, Mail, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Restablecer Contraseña" />

        <div class="mb-10 text-center">
            <div
                class="inline-flex mb-5 p-4 bg-[var(--accent)]/10 rounded-full border border-[var(--accent)]/30 shadow-[0_0_25px_var(--accent)]/20">
                <ShieldCheck class="w-10 h-10 text-[var(--accent)]" />
            </div>

            <h2 class="text-2xl font-black uppercase tracking-widest text-white italic mb-3">
                NUEVA CLAVE
            </h2>
            <p class="text-sm text-white/60 font-medium max-w-[280px] mx-auto">
                Crea una contraseña segura para volver a entrar al escenario.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-7">
            <div>
                <InputLabel for="email" value="CORREO ELECTRÓNICO"
                    class="text-white/80 text-[10px] tracking-widest mb-3" />
                <div class="relative">
                    <TextInput id="email" type="email"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white/50 cursor-not-allowed"
                        v-model="form.email" required readonly autocomplete="username" />
                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 pointer-events-none" />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="NUEVA CONTRASEÑA"
                    class="text-white/80 text-[10px] tracking-widest mb-3" />
                <div class="relative group">
                    <TextInput id="password" type="password"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.password" required autofocus autocomplete="new-password" placeholder="••••••••" />
                    <Key
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="CONFIRMAR CONTRASEÑA"
                    class="text-white/80 text-[10px] tracking-widest mb-3" />
                <div class="relative group">
                    <TextInput id="password_confirmation" type="password"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white placeholder:text-white/10"
                        v-model="form.password_confirmation" required autocomplete="new-password"
                        placeholder="••••••••" />
                    <Key
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--accent)] opacity-70 group-focus-within:opacity-100 transition-opacity" />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-4">
                <PrimaryButton class="w-full justify-center py-5 shadow-[0_15px_30px_-10px_var(--accent)]"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <span class="font-black uppercase tracking-widest italic">
                        {{ form.processing ? 'ACTUALIZANDO...' : 'ACTUALIZAR Y ENTRAR' }}
                    </span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>