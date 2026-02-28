<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, ShieldCheck, Loader2 } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Confirmar Contraseña" />

        <div class="mb-10 flex flex-col items-center text-center">
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-indigo-500/20 rounded-full animate-ping"></div>
                <div
                    class="relative p-5 bg-indigo-500/10 rounded-full border border-indigo-500/30 shadow-[0_0_30px_rgba(99,102,241,0.2)]">
                    <ShieldCheck class="w-8 h-8 text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]" />
                </div>
            </div>

            <h2 class="text-2xl font-black uppercase tracking-[0.2em] text-white mb-3">
                Zona <span class="text-indigo-500 italic">Protegida</span>
            </h2>

            <p class="text-[11px] text-white/40 font-bold uppercase tracking-widest leading-relaxed max-w-[280px]">
                Confirma tu identidad para autorizar esta operación en el sistema.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="relative">
                <InputLabel for="password" value="CONTRASEÑA DE SEGURIDAD"
                    class="text-white/60 text-[9px] font-black tracking-[0.2em] mb-3 ml-1" />

                <div class="relative group">
                    <TextInput id="password" type="password"
                        class="block w-full pl-12 bg-white/[0.03] border-white/10 text-white focus:bg-white/[0.07] focus:border-indigo-500/50 transition-all placeholder:text-white/10 py-4"
                        v-model="form.password" required autocomplete="current-password" autofocus
                        placeholder="••••••••" />

                    <Lock
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-500/40 group-focus-within:text-indigo-400 transition-colors" />
                </div>

                <InputError
                    class="mt-3 text-[10px] font-bold uppercase text-red-400/80 bg-red-500/5 p-2 rounded-lg border border-red-500/10"
                    :message="form.errors.password" />
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton
                    class="w-full justify-center py-6 text-[11px] bg-indigo-600 hover:bg-indigo-500 border-none shadow-[0_10px_30px_-10px_rgba(99,102,241,0.5)] active:scale-[0.98] transition-all"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">

                    <template v-if="form.processing">
                        <Loader2 class="animate-spin w-4 h-4 mr-2" />
                        <span class="font-black uppercase tracking-[0.2em]">Verificando...</span>
                    </template>

                    <template v-else>
                        <span class="font-black uppercase tracking-[0.2em]">Desbloquear Acceso</span>
                    </template>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-10 text-center text-[9px] text-white/20 font-bold uppercase tracking-[0.3em]">
            Cantalope Security Protocol v3.0
        </div>
    </GuestLayout>
</template>