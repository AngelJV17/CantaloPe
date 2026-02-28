<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    value: {
        type: String,
    },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);
</script>

<template>
    <label class="block mb-1.5 transition-colors duration-300" :style="{
        fontFamily: 'var(--font-family)',
        color: 'var(--text)'
    }">

        <span v-if="value" class="text-[11px] font-black uppercase tracking-[0.15em] opacity-60">
            {{ value }}
        </span>

        <span v-else class="text-[11px] font-black uppercase tracking-[0.15em] opacity-60">
            <slot />
        </span>
    </label>
</template>

<style scoped>
/* Añadimos un pequeño detalle: cuando el input de abajo tenga foco, el label brilla un poco */
label:focus-within {
    opacity: 1;
    color: var(--accent);
}
</style>