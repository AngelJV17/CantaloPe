<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const settings = computed(() => page.props.auth.settings);
</script>

<template>
    <button
        class="inline-flex items-center border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition-all duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-[var(--bg-main)] active:bg-red-700 shadow-sm"
        :style="{
            borderRadius: settings?.border_radius || '0.5rem'
        }">
        <slot />
    </button>
</template>

<style scoped>
/* Añadimos un efecto de pulsación suave al hacer hover para que se sienta premium */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

button:active {
    transform: translateY(0);
}
</style>