<script setup>
import { Search, ListFilter, ChevronDown } from 'lucide-vue-next';

defineProps({
    search: String,
    perPage: [String, Number],
    placeholder: {
        type: String,
        default: "BUSCAR POR TÍTULO O ID..."
    }
});

defineEmits(['update:search', 'update:perPage']);
</script>

<template>
    <div class="flex items-center gap-3 w-full group">

        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Search class="w-4 h-4 text-gray-500 group-focus-within:text-[var(--accent)] transition-colors" />
            </div>
            <input type="search" :value="search" @input="$emit('update:search', $event.target.value)"
                :placeholder="placeholder"
                class="w-full h-[45px] bg-black/40 border border-white/10 rounded-xl pl-12 pr-4 text-[11px] font-black text-white uppercase tracking-widest focus:border-[var(--accent)]/50 focus:ring-2 focus:ring-[var(--accent)]/10 transition-all placeholder:text-white/10 shadow-inner shadow-black" />
        </div>

        <div class="relative">
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                <ListFilter class="w-3.5 h-3.5 text-gray-500" />
            </div>
            <select :value="perPage" @change="$emit('update:perPage', $event.target.value)"
                class="appearance-none h-[45px] bg-black/40 border border-white/10 rounded-xl pl-10 pr-10 text-[11px] font-black text-white uppercase focus:border-[var(--accent)]/50 focus:ring-2 focus:ring-[var(--accent)]/10 cursor-pointer transition-all shadow-inner shadow-black">
                <option value="10" class="bg-[#12141c] text-white">10</option>
                <option value="25" class="bg-[#12141c] text-white">25</option>
                <option value="50" class="bg-[#12141c] text-white">50</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-500">
                <ChevronDown class="w-4 h-4" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Quitamos la flecha nativa de los navegadores para usar la nuestra de Lucide */
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>