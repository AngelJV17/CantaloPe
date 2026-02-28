<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { UserCircle, Mail, Fingerprint, CheckCircle2, AlertCircle } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="max-w-2xl">
        <header>
            <h2 class="text-xl font-black uppercase tracking-widest text-white italic flex items-center">
                <Fingerprint class="w-6 h-6 mr-3 text-[var(--accent)] drop-shadow-[0_0_8px_var(--accent)]" />
                Identidad del Perfil
            </h2>

            <p class="mt-2 text-sm text-white/60 leading-relaxed">
                Gestiona tu nombre artístico y la dirección de correo donde recibes las notificaciones del show.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-8 space-y-6">
            <div class="relative group">
                <InputLabel for="name" value="NOMBRE DE USUARIO"
                    class="text-white/80 text-[10px] tracking-[0.2em] mb-3" />
                <div class="relative">
                    <TextInput id="name" type="text"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white focus:bg-white/10 transition-all placeholder:text-white/10"
                        v-model="form.name" required autofocus autocomplete="name" placeholder="Tu nombre o apodo" />
                    <UserCircle
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-[var(--accent)] transition-colors" />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="relative group">
                <InputLabel for="email" value="CORREO ELECTRÓNICO"
                    class="text-white/80 text-[10px] tracking-[0.2em] mb-3" />
                <div class="relative">
                    <TextInput id="email" type="email"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white focus:bg-white/10 transition-all placeholder:text-white/10"
                        v-model="form.email" required autocomplete="username" placeholder="correo@ejemplo.com" />
                    <Mail
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-[var(--accent)] transition-colors" />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl flex items-start gap-4">
                    <AlertCircle class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-xs font-bold text-yellow-200/80 uppercase tracking-wider">
                            Tu correo no ha sido verificado.
                        </p>
                        <Link :href="route('verification.send')" method="post" as="button"
                            class="mt-2 text-xs text-white/40 underline hover:text-[var(--accent)] transition-colors font-medium">
                            Haz clic aquí para reenviar el enlace de verificación.
                        </Link>
                    </div>
                </div>

                <div v-show="status === 'verification-link-sent'"
                    class="mt-3 text-xs font-black text-green-400 uppercase tracking-widest px-4">
                    ✓ Se ha enviado un nuevo enlace a tu dirección.
                </div>
            </div>

            <div class="flex items-center gap-6 pt-4">
                <PrimaryButton class="px-10 py-4 shadow-[0_10px_25px_-5px_var(--accent)]" :disabled="form.processing">
                    <span class="font-black uppercase tracking-widest italic">Guardar Cambios</span>
                </PrimaryButton>

                <Transition enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-x-4" leave-active-class="transition duration-300 ease-in"
                    leave-to-class="opacity-0">
                    <div v-if="form.recentlySuccessful"
                        class="flex items-center text-green-400 bg-green-500/10 px-4 py-2 rounded-full border border-green-500/20">
                        <CheckCircle2 class="w-4 h-4 mr-2" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Información Actualizada</span>
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>