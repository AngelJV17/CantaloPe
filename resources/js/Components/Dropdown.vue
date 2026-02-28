<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    align: { type: String, default: 'right' },
    width: { type: String, default: '48' },
    contentClasses: {
        type: String,
        default: 'py-2', // Limpiamos los estilos fijos de aquí y los pasamos al style o clases dinámicas
    },
});

const page = usePage();
const settings = computed(() => page.props.auth.settings);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
        '64': 'w-64',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    } else if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    } else {
        return 'origin-top';
    }
});

const open = ref(false);

// Estilos dinámicos basados en la BD
const dropdownStyles = computed(() => ({
    backgroundColor: `color-mix(in srgb, ${settings.value?.sidebar_color || '#12141c'}, transparent 5%)`,
    borderRadius: settings.value?.border_radius || '1rem',
    border: '1px solid rgba(255, 255, 255, 0.1)',
}));
</script>

<template>
    <div class="relative">
        <div @click="open = !open" class="cursor-pointer">
            <slot name="trigger" />
        </div>

        <div v-show="open" class="fixed inset-0 z-40 bg-black/20 backdrop-blur-[2px]" @click="open = false"></div>

        <Transition enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 -translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2">

            <div v-show="open" class="absolute z-50 mt-3 shadow-[0_20px_50px_rgba(0,0,0,0.5)] backdrop-blur-xl"
                :class="[widthClass, alignmentClasses]" :style="dropdownStyles" @click="open = false">

                <div :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Aseguramos que el contenido herede el redondeado del padre */
div[v-show="open"] {
    overflow: hidden;
}
</style>