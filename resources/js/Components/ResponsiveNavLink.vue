<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);

// 1. CREAMOS EL VALOR DE LA SOMBRA AQUÍ
const activeShadow = computed(() => {
    if (!props.active) return 'none';

    // Extraemos el color de acento o usamos el por defecto
    const accent = settings.value?.accent_color || '#6366f1';

    // Retornamos el string exacto de CSS
    return `inset 10px 0 15px -10px color-mix(in srgb, ${accent}, transparent 60%)`;
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-3 border-l-4 border-[var(--accent)] text-start text-base font-black uppercase tracking-[0.2em] text-[var(--accent)] bg-[var(--accent)]/10 focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-3 border-l-4 border-transparent text-start text-base font-bold uppercase tracking-[0.2em] text-gray-500 hover:text-gray-200 hover:bg-white/5 hover:border-white/10 focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes" :style="{ fontFamily: 'var(--font-family)' }">
        <slot />
    </Link>
</template>

<style scoped>
a {
    /* 2. V-BIND SIMPLE A LA VARIABLE COMPUTADA */
    box-shadow: v-bind(activeShadow);
}
</style>