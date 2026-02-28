<script setup>
import { onMounted, ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// 1. Añadimos 'type' a las props para que acepte 'password', 'email', etc.
const props = defineProps({
    type: {
        type: String,
        default: 'text',
    },
});

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);
const page = usePage();

const settings = computed(() => page.props.auth.settings);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

const inputStyle = computed(() => ({
    borderRadius: 'var(--radius)',
    fontFamily: 'var(--font-family)',
}));
</script>

<template>
    <div class="relative group">
        <input
            class="custom-input block w-full bg-white/5 border-white/10 text-white focus:border-[var(--accent)] focus:ring-4 focus:ring-[var(--accent)]/20 transition-all duration-300 placeholder:text-white/10 px-4 py-3"
            :style="inputStyle" v-model="model" ref="input" :type="props.type" />
        <div
            class="absolute bottom-0 left-0 h-[2px] w-0 bg-[var(--accent)] transition-all duration-500 group-focus-within:w-full opacity-50">
        </div>
    </div>
</template>

<style scoped>
.custom-input {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    outline: none;
}

.custom-input:focus {
    background-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 20px -5px color-mix(in srgb, var(--accent), transparent 70%);
}
</style>