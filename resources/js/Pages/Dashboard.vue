<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutDashboard, Music, Users, Mic2,
    Star, PlayCircle, Clock, TrendingUp
} from 'lucide-vue-next';

const page = usePage();

// Settings globales para radio de borde y fuente
const settings = computed(() => page.props.auth.settings);

const stats = [
    { name: 'Canciones en Cola', value: '12', icon: Music, color: 'text-indigo-400', bg: 'bg-indigo-500/10' },
    { name: 'Cantantes hoy', value: '48', icon: Users, color: 'text-emerald-400', bg: 'bg-emerald-500/10' },
    { name: 'Micrófonos Activos', value: '2', icon: Mic2, color: 'text-pink-400', bg: 'bg-pink-500/10' },
    { name: 'Puntuación Media', value: '9.2', icon: Star, color: 'text-amber-400', bg: 'bg-amber-500/10' },
];
</script>

<template>

    <Head title="Panel de Control" />

    <AuthenticatedLayout>
        <template #header>
            <span>Resumen del Espectáculo</span>
        </template>

        <div id="scroll-anchor" class="relative -top-24"></div>

        <div class="max-w-6xl mx-auto pb-20 px-4 space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in stats" :key="stat.name"
                    class="p-6 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-xl transition-all duration-300 hover:border-white/10 group"
                    :style="{ borderRadius: settings?.border_radius || '1rem' }">

                    <div class="flex items-center justify-between mb-4">
                        <div :class="['p-3 rounded-2xl transition-all duration-500', stat.bg]">
                            <component :is="stat.icon" :class="['w-5 h-5', stat.color]" />
                        </div>
                        <TrendingUp class="w-4 h-4 text-white/5 group-hover:text-white/20 transition-colors" />
                    </div>

                    <div>
                        <p class="text-3xl font-bold text-white tracking-tight">{{ stat.value }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-gray-500 mt-1">
                            {{ stat.name }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-10 bg-[#12141c]/50 border border-white/5 backdrop-blur-md shadow-2xl relative overflow-hidden"
                :style="{ borderRadius: settings?.border_radius || '1.5rem' }">

                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-indigo-500/5 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">
                    <div class="space-y-6 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/40 border border-white/5 text-indigo-400 text-[10px] font-bold uppercase tracking-[0.2em]">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                            Live System Active
                        </div>

                        <h3 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter leading-none">
                            EL ESCENARIO <br />
                            <span class="text-indigo-500">ESTÁ LISTO</span>
                        </h3>

                        <p class="text-gray-400 text-sm md:text-base max-w-xl leading-relaxed">
                            Actualmente hay <span class="text-white font-bold underline decoration-indigo-500/30">12
                                canciones</span> en espera.
                            La atmósfera es ideal para el siguiente turno.
                        </p>

                        <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
                            <button
                                class="flex items-center bg-indigo-600 text-white font-bold uppercase tracking-widest px-10 py-4 text-[11px] shadow-xl shadow-indigo-500/10 hover:bg-indigo-500 transition-all active:scale-95"
                                :style="{ borderRadius: settings?.border_radius || '0.75rem' }">
                                <PlayCircle class="w-4 h-4 mr-2" />
                                Llamar Siguiente
                            </button>

                            <button
                                class="flex items-center bg-white/5 border border-white/10 text-white/70 font-bold uppercase tracking-widest px-8 py-4 text-[11px] hover:bg-white/10 transition-all"
                                :style="{ borderRadius: settings?.border_radius || '0.75rem' }">
                                <Clock class="w-4 h-4 mr-2" />
                                Ver Historial
                            </button>
                        </div>
                    </div>

                    <div class="w-full lg:w-72 aspect-square bg-black/40 border border-white/5 flex flex-col items-center justify-center relative overflow-hidden"
                        :style="{ borderRadius: settings?.border_radius || '1.5rem' }">
                        <Mic2 class="w-16 h-16 text-white/5" />

                        <div class="absolute bottom-0 left-0 right-0 flex gap-1 items-end h-16 px-6 pb-6">
                            <div v-for="i in 10" :key="i" class="flex-1 bg-indigo-500/20 rounded-t-sm animate-pulse"
                                :style="{ height: (Math.random() * 60 + 20) + '%', animationDelay: (i * 0.1) + 's' }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between px-2 pt-4">
                <div class="flex items-center gap-4">
                    <div class="h-[1px] w-12 bg-white/5"></div>
                    <p class="text-[9px] text-gray-600 font-bold uppercase tracking-[0.4em]">
                        Cantalope v3.0 // Estación de Control
                    </p>
                </div>
                <p class="text-[9px] text-indigo-500/40 font-bold uppercase tracking-[0.2em]">
                    Session ID: #K-2024
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transiciones suaves y profesionales, no nerviosas */
* {
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
}

.max-w-6xl {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>