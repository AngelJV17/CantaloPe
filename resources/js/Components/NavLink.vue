<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
    isSidebarOpen: { type: Boolean, default: true },
    icon: { type: Object }
});
</script>

<template>
    <Link :href="href"
        class="flex items-center h-12 rounded-[calc(var(--radius)*0.7)] transition-all duration-200 group relative overflow-hidden w-full"
        :class="[
            active
                ? 'bg-[var(--accent)]/10 text-[var(--accent)]'
                : 'text-gray-500 hover:text-gray-300 hover:bg-white/5'
        ]">

        <div class="flex items-center justify-center w-12 h-12 shrink-0">
            <component :is="icon" class="w-5 h-5 transition-transform group-hover:scale-110"
                :class="{ 'drop-shadow-[0_0_8px_var(--accent)]': active }" />
        </div>

        <Transition name="fade-fast">
            <span v-show="isSidebarOpen" class="text-sm font-bold tracking-wide truncate">
                <slot />
            </span>
        </Transition>

        <div v-if="active"
            class="absolute left-0 w-1 h-6 bg-[var(--accent)] rounded-full shadow-[0_0_15px_var(--accent)]">
        </div>
    </Link>
</template>

<style scoped>
.fade-fast-enter-active,
.fade-fast-leave-active {
    transition: opacity 0.1s ease;
}

.fade-fast-enter-from,
.fade-fast-leave-to {
    opacity: 0;
}
</style>