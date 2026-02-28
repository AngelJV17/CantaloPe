<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Mic2 } from 'lucide-vue-next';

const props = defineProps({
    size: { type: [String, Number], default: 45 },
    active: { type: Boolean, default: false }
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);
const customLogo = computed(() => settings.value?.logo_path);
</script>

<template>
    <div class="relative flex items-center justify-center transition-all duration-300 overflow-hidden" :style="{
        width: size + 'px',
        height: size + 'px',
        borderRadius: '50%',
        backgroundColor: active ? 'var(--accent)' : 'transparent',
        border: active ? '2px solid var(--accent)' : '2px solid transparent',
        boxShadow: active ? '0 0 15px color-mix(in srgb, var(--accent), transparent 60%)' : 'none',
        padding: customLogo ? '5px' : '0'
    }">

        <template v-if="customLogo">
            <img :src="'/storage/' + customLogo"
                class="w-full h-full object-contain transition-all duration-300 scale-[1.4]"
                :class="{ 'grayscale-0': active, 'grayscale opacity-60': !active }" />
        </template>

        <Mic2 v-else :size="size * 0.5" :style="{ color: active ? 'white' : 'currentColor' }" />
    </div>
</template>

<style scoped>
/* Transición suave para el hover si decides usarlo */
img {
    pointer-events: none;
    will-change: transform;
}

div:hover img {
    transform: scale(1.55);
    /* Un ligero aumento al pasar el mouse */
}
</style>