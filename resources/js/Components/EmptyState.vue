<script setup>
import { Music2 } from 'lucide-vue-next';
defineProps(['title', 'message']);
</script>

<template>
    <div class="py-20 flex flex-col items-center justify-center text-center opacity-30">
        <Music2 class="w-16 h-16 mb-4 text-gray-500" />
        <h3 class="text-sm font-black uppercase tracking-[0.3em] text-white">
            {{ title || 'Sin registros' }}</h3>
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-2">
            {{ message || 'No se encontró lo que buscas' }}
        </p>
    </div>
</template>