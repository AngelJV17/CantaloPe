<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-black uppercase tracking-widest text-white italic flex items-center">
                <Trash2 class="w-5 h-5 mr-2 text-red-500" />
                Eliminar Cuenta
            </h2>

            <p class="mt-2 text-sm text-white/60 leading-relaxed max-w-xl">
                Una vez que tu cuenta sea eliminada, todos sus recursos y datos se borrarán permanentemente. Por favor,
                descarga cualquier información que desees conservar antes de proceder.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion" class="px-6 py-3 uppercase font-bold tracking-widest">
            Borrar Mi Cuenta
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-[#0f111a] border border-white/10 rounded-[var(--radius)]">
                <div class="flex items-center mb-6 text-red-500">
                    <AlertTriangle class="w-8 h-8 mr-4 drop-shadow-[0_0_10px_rgba(239,68,68,0.4)]" />
                    <h2 class="text-xl font-black uppercase tracking-tight text-white italic">
                        ¿Confirmas la eliminación?
                    </h2>
                </div>

                <p class="text-sm text-white/70 leading-relaxed mb-8">
                    Esta acción es irreversible. Todos tus datos del karaoke, historial y configuraciones desaparecerán.
                    Ingresa tu contraseña para confirmar que realmente deseas irte.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña de Confirmación"
                        class="text-white/80 text-[10px] tracking-widest mb-3" />

                    <TextInput id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="block w-full bg-white/5 border-white/20 text-white placeholder:text-white/20"
                        placeholder="••••••••" @keyup.enter="deleteUser" />

                    <InputError :message="form.errors.password"
                        class="mt-3 bg-red-500/10 p-2 rounded border border-red-500/20" />
                </div>

                <div class="mt-10 flex justify-end space-x-4">
                    <SecondaryButton @click="closeModal"
                        class="border-white/20 text-white/70 hover:bg-white/10 px-6 uppercase text-[10px] font-bold tracking-widest">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton class="px-8 py-3 shadow-[0_10px_20px_-5px_rgba(239,68,68,0.4)]"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="deleteUser">
                        ELIMINAR PERMANENTEMENTE
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>