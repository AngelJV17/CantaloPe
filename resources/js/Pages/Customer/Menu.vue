<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Mic2,
    GlassWater,
    UtensilsCrossed,
    Star,
    Check,
    Music2,
    Layers,
    ChevronRight,
    User as UserIcon
} from 'lucide-vue-next';

const props = defineProps({
    table: Object,
    brandSettings: Object,
    myActiveSongs: {
        type: Array,
        default: () => []
    },
    mySongsCount: Number
});

const page = usePage();
const showSuccessModal = ref(false);

const navigateTo = (routeId) => {
    if (routeId === 'karaoke') {
        router.get(`/m/${props.table.identifier}/cantar`);
    }
};

onMounted(() => {
    if (page.props.flash?.success) {
        showSuccessModal.value = true;
        setTimeout(() => {
            showSuccessModal.value = false;
        }, 3500);
    }
});

watch(() => page.props.flash?.success, (newVal) => {
    if (newVal) {
        showSuccessModal.value = true;
        setTimeout(() => {
            showSuccessModal.value = false;
        }, 3500);
    }
});

const menuOptions = [
    {
        id: 'karaoke',
        title: 'Cantar',
        desc: 'Pide tu canción ahora',
        icon: Mic2,
        color: '#6366f1'
    },
    {
        id: 'drinks',
        title: 'Bebidas',
        desc: 'Tragos y Cervezas',
        icon: GlassWater,
        color: '#ec4899'
    },
    {
        id: 'food',
        title: 'Comida',
        desc: 'Piqueos y Snacks',
        icon: UtensilsCrossed,
        color: '#10b981'
    }
];
</script>

<template>

    <Head :title="`Mesa ${table.identifier} - ${brandSettings.local_name}`" />

    <div class="min-h-screen bg-[#06070a] text-white p-5 select-none"
        :style="{ fontFamily: brandSettings.font_family || 'Inter, sans-serif' }">

        <!-- Header Principal -->
        <header class="pt-8 pb-10 text-center relative">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/10 to-transparent -z-10"></div>
            <h1 class="text-3xl font-black uppercase tracking-tighter italic"
                :style="{ color: brandSettings.accent_color }">
                {{ brandSettings.local_name }}
            </h1>
            <div
                class="inline-flex items-center gap-2 mt-3 px-4 py-1.5 bg-white/5 rounded-full border border-white/5 backdrop-blur-md">
                <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Mesa #{{ table.identifier
                    }}</span>
            </div>
        </header>

        <main class="max-w-md mx-auto space-y-8 pb-10">

            <!-- SECCIÓN: ESTADO DE COLA -->
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 flex items-center gap-2">
                        <Layers class="w-3.5 h-3.5" />
                        Tu actividad
                    </h3>
                    <span
                        class="text-[9px] font-bold bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded border border-indigo-500/20">
                        {{ mySongsCount }} Pedidos
                    </span>
                </div>

                <div v-if="myActiveSongs && myActiveSongs.length > 0" class="space-y-3">
                    <div v-for="(song, index) in myActiveSongs" :key="song.id"
                        class="relative overflow-hidden flex items-center justify-between p-4 bg-[#12141c] border border-white/5 rounded-3xl transition-all active:scale-[0.98]"
                        :class="index === 0 ? 'ring-1 ring-indigo-500/50 shadow-2xl' : ''">

                        <div v-if="index === 0" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>

                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0"
                                :class="index === 0 ? 'bg-indigo-500 text-white shadow-lg' : 'bg-white/5 text-gray-500'">
                                <Music2 class="w-5 h-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-black truncate text-white uppercase italic tracking-tight">
                                    {{ song.title || 'Preparando...' }}
                                </h4>
                                <div class="flex items-center gap-1.5 mt-0.5 opacity-60">
                                    <UserIcon class="w-3 h-3 text-indigo-400" />
                                    <span class="text-[10px] font-bold uppercase tracking-wider truncate">
                                        {{ song.customer_name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="ml-4 flex flex-col items-center justify-center bg-white/5 px-3 py-2 rounded-2xl min-w-[55px] border border-white/5">
                            <span
                                class="text-[8px] font-black text-gray-500 uppercase leading-none mb-1 text-center">Turno</span>
                            <span class="text-lg font-black leading-none"
                                :class="index === 0 ? 'text-indigo-400' : 'text-white'">
                                #{{ song.position }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else
                    class="py-10 text-center bg-white/[0.02] rounded-[2.5rem] border border-dashed border-white/10">
                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">No tienes canciones en
                        cola</p>
                </div>
            </section>

            <!-- SECCIÓN: ACCIONES -->
            <div class="grid gap-3 pt-4">
                <button v-for="option in menuOptions" :key="option.id" @click="navigateTo(option.id)"
                    class="group relative flex items-center gap-4 p-5 bg-gradient-to-r from-[#12141c] to-[#0d0f14] border border-white/5 rounded-3xl text-left transition-all active:scale-[0.95] hover:border-white/10">

                    <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-110"
                        :style="{ color: option.color }">
                        <component :is="option.icon" class="w-6 h-6" />
                    </div>

                    <div class="flex-1">
                        <h2 class="text-base font-black uppercase tracking-tight">{{ option.title }}</h2>
                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-widest mt-0.5 leading-none">
                            {{ option.desc }}
                        </p>
                    </div>

                    <div
                        class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center opacity-30 group-hover:opacity-100 transition-opacity">
                        <ChevronRight class="w-4 h-4" />
                    </div>
                </button>

                <!-- Tarjeta VIP -->
                <button
                    class="relative overflow-hidden flex items-center justify-between p-6 bg-gradient-to-br from-yellow-500/20 via-yellow-500/5 to-transparent border border-yellow-500/20 rounded-3xl group active:scale-[0.95]">
                    <div class="absolute -right-6 -bottom-6 opacity-10 transition-transform group-hover:scale-110">
                        <Star class="w-24 h-24 fill-yellow-500" />
                    </div>

                    <div class="flex items-center gap-4 relative z-10">
                        <div
                            class="w-12 h-12 bg-yellow-500 rounded-2xl flex items-center justify-center text-black shadow-[0_0_20px_rgba(234,179,8,0.3)]">
                            <Star class="w-6 h-6 fill-current" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-yellow-500 uppercase italic leading-none">Pase VIP</h3>
                            <p
                                class="text-[9px] text-yellow-500/60 uppercase font-bold tracking-widest mt-1.5 leading-none">
                                Salta la fila de espera</p>
                        </div>
                    </div>

                    <div class="bg-yellow-500 text-black px-3 py-1 rounded-lg relative z-10">
                        <span class="text-[10px] font-black italic">TOP</span>
                    </div>
                </button>
            </div>
        </main>

        <!-- Modal Success -->
        <Transition name="fade">
            <div v-if="showSuccessModal"
                class="fixed inset-0 z-[1000] bg-black/95 backdrop-blur-2xl flex items-center justify-center p-8 text-center">
                <div class="space-y-6">
                    <div
                        class="mx-auto w-24 h-24 bg-indigo-500 rounded-[2.5rem] flex items-center justify-center shadow-[0_0_50px_rgba(99,102,241,0.5)] animate-bounce">
                        <Check class="w-12 h-12 text-white stroke-[4px]" />
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-4xl font-black uppercase tracking-tighter italic">¡Recibido!</h2>
                        <p class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.4em]">Tu canción ya está
                            en cola</p>
                    </div>
                </div>
            </div>
        </Transition>

        <footer class="py-10 text-center opacity-10">
            <p class="text-[8px] font-black uppercase tracking-[0.8em]">Cantalope Experience</p>
        </footer>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>