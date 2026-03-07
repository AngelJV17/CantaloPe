<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';

const props = defineProps({
    current: {
        type: Object,
        default: () => ({
            customer_name: 'ANGEL',
            service_table: { identifier: 'M-05' },
            song: { title: 'MARIPOSA TRAICIONERA (VERSIÓN)', artist: 'MANÁ', youtube_id: 'dQw4w9WgXcQ' }
        })
    },
    next: {
        type: Object,
        default: () => ({
            customer_name: 'LUIS',
            song: { title: 'CUATRO MENTIRAS', artist: 'CORAZÓN SERRANO' }
        })
    },
    settings: {
        type: Object,
        default: () => ({
            local_name: 'PISO 2',
            primary_color: '#00f2ff', // Cyan vibrante
            accent_color: '#7000ff'   // Púrpura neón
        })
    }
});

const player = ref(null);
const isReady = ref(false);

const initYouTube = () => {
    if (!props.current?.song?.youtube_id) return;

    if (window.YT && window.YT.Player) {
        if (player.value) {
            try { player.value.destroy(); } catch (e) { console.error(e); }
        }

        player.value = new window.YT.Player('youtube-player', {
            height: '100%',
            width: '100%',
            videoId: props.current.song.youtube_id,
            playerVars: {
                autoplay: 1,
                controls: 0,
                modestbranding: 1,
                rel: 0,
                showinfo: 0,
                iv_load_policy: 3,
                disablekb: 1,
                loop: 1,
                playlist: props.current.song.youtube_id
            },
            events: {
                onReady: () => { isReady.value = true; },
            }
        });
    }
};

onMounted(() => {
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        window.onYouTubeIframeAPIReady = initYouTube;
    } else {
        initYouTube();
    }
});

onBeforeUnmount(() => {
    if (player.value) player.value.destroy();
});

// Estilo de sombra de alto contraste para textos de color
const highContrastShadow = computed(() => ({
    textShadow: `
        -2px -2px 0 #000,  
         2px -2px 0 #000,
        -2px  2px 0 #000,
         2px  2px 0 #000,
         0px  4px 10px rgba(0,0,0,0.8)
    `
}));
</script>

<template>
    <div class="fixed inset-0 bg-black text-white flex flex-col overflow-hidden font-sans select-none">

        <!-- HEADER: ESTILO BROADCAST -->
        <div
            class="absolute top-0 left-0 right-0 z-50 p-8 flex justify-between items-start bg-gradient-to-b from-black/90 via-black/40 to-transparent">
            <div class="flex gap-4 items-center">
                <div class="flex flex-col border-l-4 pl-4" :style="{ borderColor: settings.primary_color }">
                    <span class="text-[10px] tracking-[0.4em] font-black text-zinc-400 uppercase">Karaoke Live</span>
                    <span
                        class="text-4xl font-black tracking-tighter italic uppercase drop-shadow-[0_2px_2px_rgba(0,0,0,1)]">
                        {{ settings.local_name }}
                    </span>
                </div>
            </div>

            <div
                class="flex items-center gap-4 bg-black/80 backdrop-blur-xl px-5 py-2.5 rounded-2xl border border-white/20 shadow-2xl">
                <div class="relative flex h-3 w-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-500"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                </div>
                <span class="text-xs font-black uppercase tracking-[0.2em] text-white">En Vivo</span>
            </div>
        </div>

        <!-- AREA CENTRAL: VIDEO -->
        <div class="flex-1 relative overflow-hidden bg-zinc-900">
            <div id="youtube-player" class="absolute inset-0 w-full h-full scale-[1.05]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90 z-10">
            </div>
        </div>

        <!-- FOOTER: INFO DEL CANTANTE -->
        <div
            class="h-44 bg-[#050505] relative z-20 grid grid-cols-12 border-t border-white/10 shadow-[0_-20px_50px_rgba(0,0,0,0.5)]">

            <!-- PANEL IZQUIERDO: CANTANTE ACTUAL -->
            <div class="col-span-8 flex items-center px-10 gap-8 relative overflow-hidden">
                <!-- Marca de agua de mesa -->
                <div
                    class="absolute -left-4 top-0 bottom-0 flex items-center justify-center opacity-[0.05] pointer-events-none">
                    <span class="text-[14rem] font-black italic">{{ current?.service_table?.identifier || '00' }}</span>
                </div>

                <!-- Card de Mesa con el texto ajustado -->
                <div
                    class="relative flex flex-col items-center justify-center min-w-[120px] h-[120px] rounded-3xl border-2 border-white/60 bg-neutral-900 shadow-2xl overflow-hidden">

                    <!-- Glow de fondo -->
                    <div class="absolute inset-0 opacity-20" :style="{ backgroundColor: settings.primary_color }"></div>

                    <div class="relative flex flex-col items-center justify-center">
                        <span class="text-sm font-black text-white/70 uppercase tracking-[0.3em] mb-1">MESA</span>

                        <!-- EL TEXTO CON BORDE CLARO -->
                        <span class="text-5xl font-black tracking-tighter m-2"
                            :style="{ color: settings.accent_color, ...highContrastShadow }">
                            {{ current?.service_table?.identifier || '--' }}
                        </span>
                    </div>
                </div>

                <!-- Info Cantante -->
                <div class="flex-1 min-w-0 z-10">
                    <div class="flex items-center gap-3 mb-1">
                        <span
                            class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-white text-black shadow-lg">
                            CANTANDO AHORA
                        </span>
                        <div class="h-[2px] flex-1 bg-gradient-to-r from-white/40 to-transparent"></div>
                    </div>

                    <h2
                        class="text-7xl font-black italic tracking-tighter uppercase truncate leading-[0.9] py-1 drop-shadow-[0_5px_15px_rgba(0,0,0,1)]">
                        {{ current?.customer_name }}
                    </h2>

                    <div class="flex items-center gap-2 mt-2">
                        <div class="bg-zinc-800 px-2 py-1 rounded">
                            <span class="text-[10px] font-black uppercase text-zinc-400 tracking-widest">TEMA</span>
                        </div>
                        <p class="text-xl font-bold text-white uppercase truncate italic drop-shadow-md">
                            {{ current?.song?.title }}
                            <span :style="{ color: settings.primary_color }" class="mx-2 font-black">/</span>
                            <span class="text-zinc-400">{{ current?.song?.artist }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- PANEL DERECHO: PRÓXIMO -->
            <div class="col-span-4 bg-zinc-900/60 border-l border-white/10 flex flex-col justify-center px-10 relative">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-sm font-black uppercase tracking-[0.2em] text-zinc-400">
                            SIGUIENTE
                        </span>
                        <div class="h-[3px] w-12 rounded-full" :style="{ backgroundColor: settings.accent_color }">
                        </div>
                    </div>
                    <div v-if="next" class="space-y-2">
                        <!-- Mesa del siguiente -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-widest text-zinc-500">
                                Mesa:
                            </span>
                            <span class="px-2 py-[2px] rounded-md text-sm font-black tracking-wider" :style="{
                                backgroundColor: settings.accent_color + '20',
                                color: settings.accent_color,
                                boxShadow: `0 0 10px ${settings.accent_color}55`
                            }">
                                {{ next?.service_table?.identifier || '--' }}
                            </span>
                        </div>
                        <!-- Nombre -->
                        <h3
                            class="text-3xl font-black tracking-tight text-white uppercase italic leading-none drop-shadow-lg">
                            {{ next.customer_name }}
                        </h3>
                        <!-- Canción -->
                        <p class="text-sm font-bold text-zinc-400 uppercase truncate italic mt-1">
                            {{ next.song.title }}
                        </p>
                    </div>
                    <div v-else class="text-xs font-bold text-zinc-600 uppercase italic tracking-widest">
                        Preparando lista...
                    </div>
                </div>

                <!-- Decorativo Lateral -->
                <div
                    class="absolute right-0 top-0 bottom-0 w-1 bg-gradient-to-b from-transparent via-white/20 to-transparent">
                </div>
            </div>
        </div>

        <!-- TICKER INFERIOR -->
        <div class="h-10 bg-black flex items-center overflow-hidden border-t-2 border-white/5">
            <div class="ticker-content flex items-center gap-24 whitespace-nowrap px-10">
                <div v-for="n in 3" :key="n" class="flex items-center gap-24">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 rotate-45" :style="{ backgroundColor: settings.primary_color }"></div>
                        <span class="text-[12px] font-black uppercase tracking-[0.3em] text-zinc-300">
                            {{ settings.local_name }} - SONIDO PROFESIONAL
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 rotate-45 bg-white"></div>
                        <span
                            class="text-[12px] font-black uppercase tracking-[0.3em] :style='{ color: settings.primary_color }'">
                            ESCANEA EL QR Y PIDE TU CANCIÓN
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif !important;
}

#youtube-player :deep(iframe) {
    pointer-events: none;
    border: none;
}

@keyframes ticker {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-50%);
    }
}

.ticker-content {
    animation: ticker 40s linear infinite;
}

/* Forzar ocultamiento de elementos de pausa de YT */
:deep(.ytp-pause-overlay),
:deep(.ytp-expand-pause-overlay) {
    display: none !important;
}
</style>