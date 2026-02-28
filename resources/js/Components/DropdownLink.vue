<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String },
    as: { type: String, default: 'a' },
    method: { type: String, default: 'get' },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);

// Estilos dinámicos para el hover y el radio de borde
const dynamicStyles = computed(() => ({
    '--hover-bg': `color-mix(in srgb, ${settings.value?.accent_color || '#6366f1'}, transparent 85%)`,
    '--accent': settings.value?.accent_color || '#6366f1',
    '--radius': `calc(${settings.value?.border_radius || '1rem'} * 0.6)`
}));
</script>

<template>
    <component :is="as === 'button' ? 'button' : Link" :href="href" :method="method" :style="dynamicStyles"
        class="dropdown-link block w-[calc(100%-8px)] mx-auto px-4 py-2.5 text-start text-sm leading-5 transition-all duration-200 ease-in-out focus:outline-none group mb-0.5 last:mb-0">
        <div class="flex items-center transition-transform duration-200 group-hover:translate-x-1 font-medium">
            <slot />
        </div>
    </component>
</template>

<style scoped>
.dropdown-link {
    border-radius: var(--radius);
    color: rgba(255, 255, 255, 0.7);
}

.dropdown-link:hover {
    background-color: var(--hover-bg);
    color: var(--accent);
}

/* Si el link está dentro de un botón (como cerrar sesión), aseguramos el cursor pointer */
button.dropdown-link {
    cursor: pointer;
}

/* Efecto de foco para accesibilidad */
.dropdown-link:focus {
    background-color: var(--hover-bg);
    box-shadow: 0 0 0 1px var(--accent) inset;
}
</style>