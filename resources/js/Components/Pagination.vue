<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({ links: Array });

// Función para limpiar el label de Laravel (quita los &laquo; y &raquo;)
const cleanLabel = (label) => {
    if (label.includes('Previous')) return 'Ant';
    if (label.includes('Next')) return 'Sig';
    return label;
};
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center items-center gap-2 mt-2">
        <template v-for="(link, k) in links" :key="k">

            <div v-if="link.url === null"
                class="min-w-[40px] h-10 flex items-center justify-center px-3 text-[10px] font-black text-white/10 uppercase border border-white/5 rounded-xl bg-white/[0.02] cursor-not-allowed">
                <ChevronLeft v-if="link.label.includes('Previous')" class="w-4 h-4" />
                <ChevronRight v-else-if="link.label.includes('Next')" class="w-4 h-4" />
                <span v-else>{{ link.label }}</span>
            </div>

            <Link v-else :href="link.url"
                class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[10px] font-black uppercase border rounded-xl transition-all active:scale-90 hover:shadow-lg"
                :class="{
                    'bg-[var(--accent)] text-white border-[var(--accent)] shadow-[0_0_20px_rgba(0,0,0,0.4)] z-10': link.active,
                    'text-gray-500 border-white/5 bg-black/20 hover:border-white/20 hover:text-white': !link.active
                }" preserve-scroll>

                <ChevronLeft v-if="link.label.includes('Previous')" class="w-4 h-4" />
                <ChevronRight v-else-if="link.label.includes('Next')" class="w-4 h-4" />
                <span v-else>{{ link.label }}</span>
            </Link>

        </template>
    </div>
</template>