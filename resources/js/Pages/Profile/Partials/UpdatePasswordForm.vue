<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShieldCheck, KeyRound, CheckCircle2 } from 'lucide-vue-next';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="max-w-2xl">
        <header>
            <h2 class="text-xl font-black uppercase tracking-widest text-white italic flex items-center">
                <ShieldCheck class="w-6 h-6 mr-3 text-[var(--accent)] drop-shadow-[0_0_8px_var(--accent)]" />
                Seguridad de la Cuenta
            </h2>

            <p class="mt-2 text-sm text-white/60 leading-relaxed">
                Asegúrate de usar una contraseña larga y aleatoria para mantener tu acceso al show protegido.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-8 space-y-6">
            <div class="relative group">
                <InputLabel for="current_password" value="CONTRASEÑA ACTUAL"
                    class="text-white/80 text-[10px] tracking-[0.2em] mb-3" />
                <div class="relative">
                    <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                        type="password"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white focus:bg-white/10 transition-all"
                        autocomplete="current-password" placeholder="••••••••" />
                    <KeyRound
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-[var(--accent)] transition-colors" />
                </div>
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <hr class="border-white/5 my-8" />

            <div class="relative group">
                <InputLabel for="password" value="NUEVA CONTRASEÑA"
                    class="text-white/80 text-[10px] tracking-[0.2em] mb-3" />
                <div class="relative">
                    <TextInput id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white focus:bg-white/10 transition-all"
                        autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
                    <KeyRound
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-[var(--accent)] transition-colors" />
                </div>
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="relative group">
                <InputLabel for="password_confirmation" value="CONFIRMAR NUEVA CONTRASEÑA"
                    class="text-white/80 text-[10px] tracking-[0.2em] mb-3" />
                <div class="relative">
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                        class="block w-full pl-12 bg-white/5 border-white/20 text-white focus:bg-white/10 transition-all"
                        autocomplete="new-password" placeholder="Repite tu contraseña" />
                    <KeyRound
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-[var(--accent)] transition-colors" />
                </div>
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-6 pt-4">
                <PrimaryButton class="px-10 py-4 shadow-[0_10px_25px_-5px_var(--accent)]" :disabled="form.processing">
                    <span class="font-black uppercase tracking-widest italic">Actualizar Llave</span>
                </PrimaryButton>

                <Transition enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-x-4" leave-active-class="transition duration-300 ease-in"
                    leave-to-class="opacity-0">
                    <div v-if="form.recentlySuccessful"
                        class="flex items-center text-green-400 bg-green-500/10 px-4 py-2 rounded-full border border-green-500/20 shadow-[0_0_15px_rgba(34,197,94,0.1)]">
                        <CheckCircle2 class="w-4 h-4 mr-2" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Cambios Guardados</span>
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>