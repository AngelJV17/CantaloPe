<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const settings = computed(() => page.props.auth.settings);

// Calculamos los estilos dinámicos para el botón
const buttonStyle = computed(() => ({
    backgroundColor: 'var(--accent)',
    borderRadius: 'var(--radius)', // Usamos el radio de la BD
    fontFamily: 'var(--font-family)',
    // Creamos un sombreado que brilla con el color de acento
    boxShadow: '0 8px 20px -6px color-mix(in srgb, var(--accent), transparent 60%)',
}));
</script>

<template>
    <button :style="buttonStyle"
        class="primary-btn inline-flex items-center px-8 py-4 text-white font-black text-[11px] uppercase tracking-[0.2em] transition-all duration-300 active:scale-95 disabled:opacity-50 disabled:pointer-events-none group overflow-hidden relative">
        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        </div>

        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </button>
</template>

<style scoped>
.primary-btn {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.primary-btn:hover {
    transform: translateY(-2px);
    /* Intensificamos el brillo neón al hacer hover */
    box-shadow: 0 12px 25px -5px color-mix(in srgb, var(--accent), transparent 40%);
    filter: brightness(1.1);
}

/* Efecto de "pulso" sutil cuando el botón está activo */
.primary-btn:active {
    filter: brightness(0.9);
}
</style>