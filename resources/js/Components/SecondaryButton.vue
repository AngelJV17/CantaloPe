<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    type: {
        type: String,
        default: 'button',
    },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);

// Estilo dinámico basado en la configuración del local
const buttonStyle = computed(() => ({
    borderRadius: 'var(--radius)',
    fontFamily: 'var(--font-family)',
    borderColor: 'rgba(255, 255, 255, 0.1)',
    backgroundColor: 'rgba(255, 255, 255, 0.03)',
}));
</script>

<template>
    <button :type="type" :style="buttonStyle"
        class="inline-flex items-center px-6 py-3 border text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 transition-all duration-300 hover:bg-white/10 hover:text-white hover:border-white/20 focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/50 disabled:opacity-25 active:scale-95 group">
        <slot />
    </button>
</template>

<style scoped>
button {
    /* Un efecto de desenfoque sutil detrás del botón para que parezca cristalizado */
    backdrop-filter: blur(4px);
}

button:hover {
    /* Un brillo muy tenue en el texto al pasar el mouse */
    text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
}
</style>