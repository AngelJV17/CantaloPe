<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const emit = defineEmits(['update:checked']);
const props = defineProps({
    checked: { type: [Array, Boolean], required: true },
    value: { default: null },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);

const proxyChecked = computed({
    get() { return props.checked; },
    set(val) { emit('update:checked', val); },
});
</script>

<template>
    <input type="checkbox" :value="value" v-model="proxyChecked" class="dynamic-checkbox" :style="{
        '--accent': settings?.accent_color || '#6366f1',
        '--radius': settings?.border_radius || '0.5rem'
    }" />
</template>

<style scoped>
.dynamic-checkbox {
    /* Estilo Base */
    appearance: none;
    width: 1.25rem;
    height: 1.25rem;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: calc(var(--radius) * 0.3);
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

/* Estado: Marcado (Checked) */
.dynamic-checkbox:checked {
    background-color: var(--accent);
    border-color: var(--accent);
}

/* El "Ganchito" del check (usando un pseudo-elemento para control total) */
.dynamic-checkbox:checked::after {
    content: '';
    position: absolute;
    left: 7px;
    top: 3px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Estado: Focus (El brillo exterior) */
.dynamic-checkbox:focus {
    outline: none;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent), transparent 70%);
    border-color: var(--accent);
}

/* Hover sutil */
.dynamic-checkbox:hover:not(:checked) {
    border-color: var(--accent);
    background-color: rgba(255, 255, 255, 0.1);
}
</style>