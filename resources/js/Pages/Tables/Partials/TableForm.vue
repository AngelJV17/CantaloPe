<script setup>
import { Loader2, ChevronDown } from 'lucide-vue-next';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

defineProps({
    form: Object,
    isEditing: Boolean
});

defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6 p-6">
        <div class="grid grid-cols-1 gap-5">

            <div class="space-y-2">
                <InputLabel for="name" value="Alias de la Mesa"
                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1" />
                <input id="name" v-model="form.name" type="text" placeholder="EJ: TERRAZA VIP 01"
                    class="w-full bg-black/40 border-white/5 rounded-2xl text-white text-sm focus:border-[var(--accent)] focus:ring-4 focus:ring-[var(--accent)]/10 transition-all p-4 font-bold uppercase placeholder:text-gray-700">
                <InputError :message="form.errors.name" class="mt-1 ml-1 text-[9px] uppercase italic font-black" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <InputLabel for="identifier" value="Identificador"
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1" />
                    <input id="identifier" v-model="form.identifier" type="text" placeholder="M-01"
                        class="w-full bg-black/40 border-white/5 rounded-2xl text-white text-sm focus:border-[var(--accent)] focus:ring-0 p-4 font-mono">
                    <InputError :message="form.errors.identifier" />
                </div>
                <div class="space-y-2">
                    <InputLabel for="capacity" value="Capacidad (PAX)"
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1" />
                    <input id="capacity" v-model="form.capacity" type="number"
                        class="w-full bg-black/40 border-white/5 rounded-2xl text-white text-sm focus:border-[var(--accent)] focus:ring-0 p-4">
                    <InputError :message="form.errors.capacity" />
                </div>
            </div>

            <div class="space-y-2">
                <InputLabel value="Ubicación de Sala"
                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1" />
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 right-4 flex items-center pointer-events-none group-focus-within:text-[var(--accent)] transition-colors text-gray-600">
                        <ChevronDown class="w-5 h-5" />
                    </div>

                    <select v-model="form.zone"
                        class="w-full bg-black/40 border border-white/5 rounded-2xl text-white text-sm p-4 pr-12 appearance-none font-bold uppercase cursor-pointer hover:bg-black/60 transition-all focus:border-[var(--accent)] focus:ring-4 focus:ring-[var(--accent)]/10">
                        <option value="General" class="bg-[#1a1c26] text-white">🔲 Área General</option>
                        <option value="VIP" class="bg-[#1a1c26] text-white">💎 Zona VIP</option>
                        <option value="Terraza" class="bg-[#1a1c26] text-white">🌿 Terraza Exterior</option>
                        <option value="Bar" class="bg-[#1a1c26] text-white">🍹 Barra Principal</option>
                    </select>
                </div>
                <InputError :message="form.errors.zone" />
            </div>
        </div>

        <button type="submit" :disabled="form.processing"
            class="w-full py-5 bg-[var(--accent)] text-white rounded-2xl font-black text-xs uppercase shadow-lg shadow-[var(--accent)]/20 flex justify-center items-center gap-3 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 mt-4">
            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
            <span v-else>{{ isEditing ? 'Actualizar Parámetros' : 'Confirmar y Activar' }}</span>
        </button>
    </form>
</template>