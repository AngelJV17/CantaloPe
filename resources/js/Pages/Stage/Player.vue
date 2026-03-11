<script setup>

import { ref, onMounted, onBeforeUnmount } from "vue"
import axios from "axios"

const props = defineProps({
    current: Object,
    next: Object,
    settings: Object
})

const playerA = ref(null)
const playerB = ref(null)

const activePlayer = ref("A")

const currentSong = ref(props.current)
const nextSong = ref(props.next)

const transitionSong = ref(null)
const showTransition = ref(false)

let isTransitioning = false
let progressTimer = null
let channel = null

const ANNOUNCE_BEFORE_END = 3

/*
|--------------------------------------------------------------------------
| YOUTUBE PLAYERS
|--------------------------------------------------------------------------
*/

function createPlayers() {

    playerA.value = new YT.Player("youtube-player-a", {
        height: "100%",
        width: "100%",
        videoId: currentSong.value?.song?.youtube_id ?? null,
        playerVars: {
            autoplay: 1,
            controls: 0,
            modestbranding: 1,
            rel: 0,
            fs: 0,
            disablekb: 1,
            iv_load_policy: 3,
            playsinline: 1,
            origin: window.location.origin
        },
        events: {
            onStateChange: onPlayerStateChange
        }
    })

    playerB.value = new YT.Player("youtube-player-b", {
        height: "100%",
        width: "100%",
        playerVars: {
            autoplay: 0,
            controls: 0
        }
    })

}

/*
|--------------------------------------------------------------------------
| PROGRESS WATCHER
|--------------------------------------------------------------------------
*/

function startProgressWatcher() {

    clearInterval(progressTimer)

    progressTimer = setInterval(() => {

        const player =
            activePlayer.value === "A"
                ? playerA.value
                : playerB.value

        if (!player) return

        const duration = player.getDuration()
        const current = player.getCurrentTime()

        if (!duration) return

        const remaining = duration - current

        if (remaining <= ANNOUNCE_BEFORE_END && !showTransition.value) {
            handleSongEnd()
        }

    }, 500)

}

/*
|--------------------------------------------------------------------------
| PLAYER EVENTS
|--------------------------------------------------------------------------
*/

function onPlayerStateChange(event) {

    if (event.data === YT.PlayerState.PLAYING) {
        startProgressWatcher()
    }

    if (event.data === YT.PlayerState.ENDED) {
        event.target.clearVideo()
    }

}

/*
|--------------------------------------------------------------------------
| SONG END FLOW
|--------------------------------------------------------------------------
*/

async function handleSongEnd() {

    if (isTransitioning) return

    isTransitioning = true

    transitionSong.value = nextSong.value ?? currentSong.value
    showTransition.value = true

    clearInterval(progressTimer)

    setTimeout(async () => {

        await finishSong()

        switchPlayers()

        setTimeout(() => {

            showTransition.value = false
            transitionSong.value = null
            isTransitioning = false

        }, 800)

    }, 3500)

}

/*
|--------------------------------------------------------------------------
| FINISH SONG
|--------------------------------------------------------------------------
*/

async function finishSong() {

    if (!currentSong.value?.id) return

    const { data } = await axios.post(`/stage/${currentSong.value.id}/finish`)

    if (!data?.success) return

    currentSong.value = data.current
    nextSong.value = data.next

}

/*
|--------------------------------------------------------------------------
| SWITCH PLAYER
|--------------------------------------------------------------------------
*/

function switchPlayers() {

    if (!currentSong.value?.song?.youtube_id) return

    const video = currentSong.value.song.youtube_id

    if (activePlayer.value === "A") {

        playerB.value.loadVideoById(video)

        activePlayer.value = "B"

        setTimeout(() => {
            playerB.value.playVideo()
        }, 150)

        preloadNext(playerA.value)

    } else {

        playerA.value.loadVideoById(video)

        activePlayer.value = "A"

        setTimeout(() => {
            playerA.value.playVideo()
        }, 150)

        preloadNext(playerB.value)

    }

}

/*
|--------------------------------------------------------------------------
| PRELOAD NEXT SONG
|--------------------------------------------------------------------------
*/

function preloadNext(player) {

    if (!nextSong.value?.song?.youtube_id) return

    player.cueVideoById({
        videoId: nextSong.value.song.youtube_id
    })

}

/*
|--------------------------------------------------------------------------
| REALTIME LISTENER (REVERB)
|--------------------------------------------------------------------------
*/

function initRealtime() {

    if (!window.Echo) {
        console.error("Echo no disponible")
        return
    }

    channel = window.Echo.private(`karaoke.${props.settings.user_id}`)

    channel.listen(".queue.updated", (event) => {

        console.log("Realtime QueueUpdated:", event)

        const playing = event.fullQueue.find(q => q.status === "playing")
        const next = event.fullQueue.find(q => q.status === "pending" || q.status === "ready")

        if (playing) {

            const newVideo = playing?.song?.youtube_id
            const currentVideo = currentSong.value?.song?.youtube_id

            currentSong.value = playing
            nextSong.value = next

            if (newVideo && newVideo !== currentVideo) {
                switchPlayers()
            }

        }

        if (next) {
            nextSong.value = next
        }

    })

}

/*
|--------------------------------------------------------------------------
| YOUTUBE API
|--------------------------------------------------------------------------
*/

function initYouTube() {

    createPlayers()

    if (nextSong.value) {
        preloadNext(playerB.value)
    }

}

function loadYouTubeAPI() {

    if (window.YT) {
        initYouTube()
        return
    }

    const tag = document.createElement("script")
    tag.src = "https://www.youtube.com/iframe_api"

    document.body.appendChild(tag)

    window.onYouTubeIframeAPIReady = initYouTube

}

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(() => {

    loadYouTubeAPI()
    initRealtime()

})

onBeforeUnmount(() => {

    if (playerA.value) playerA.value.destroy()
    if (playerB.value) playerB.value.destroy()

    if (window.Echo) {
        window.Echo.leave(`karaoke.${props.settings.user_id}`)
    }

})

</script>

<template>
    <div class="fixed inset-0 bg-black flex flex-col overflow-hidden font-sans select-none cursor-none text-white">

        <!-- SECCIÓN SUPERIOR: REPRODUCTOR DE VIDEO -->
        <div class="relative flex-grow bg-black overflow-hidden">
            <div id="youtube-player-a" class="absolute inset-0 transition-opacity duration-700"
                :class="activePlayer === 'A' ? 'opacity-100' : 'opacity-0 pointer-events-none'">
            </div>
            <div id="youtube-player-b" class="absolute inset-0 transition-opacity duration-700"
                :class="activePlayer === 'B' ? 'opacity-100' : 'opacity-0 pointer-events-none'">
            </div>

            <!-- Interstitial de Próximo Turno -->
            <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-500 ease-in"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="showTransition"
                    class="absolute inset-0 z-[9999] flex items-center justify-center bg-black/95 backdrop-blur-sm overflow-hidden">

                    <!-- LUCES DE ESCENARIO -->
                    <div class="absolute inset-0 stage-lights"></div>

                    <div class="relative text-center z-10">

                        <p class="text-amber-400 text-xl font-black uppercase tracking-[0.35em] mb-8 animate-pulse">
                            Siguiente en el Escenario
                        </p>

                        <!-- NOMBRE -->
                        <h1 class="text-[140px] font-black uppercase tracking-tight text-white drop-shadow-[0_0_60px_rgba(255,255,255,0.9)]">
                            {{ transitionSong?.customer_name }}
                        </h1>

                        <!-- MESA -->
                        <div class="mt-6 text-4xl text-white/90 italic tracking-wide">
                            Mesa {{ transitionSong?.service_table?.identifier }}
                        </div>

                        <!-- CANCIÓN -->
                        <div class="mt-6 text-3xl text-white/80 italic">
                            "{{ transitionSong?.song?.title }}"
                        </div>

                        <!-- APLAUSOS -->
                        <div class="mt-12 text-6xl applause">
                            👏 👏 👏
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- SECCIÓN INFERIOR: FOOTER & BANNER -->
        <div v-if="!showTransition" class="flex flex-col z-50 shadow-[0_-10px_40px_rgba(0,0,0,0.8)]">

            <!-- CONTENEDOR PRINCIPAL DEL FOOTER (HUD) -->
            <div class="h-28 bg-[#0a0a0a] border-t border-white/5 flex items-center px-10 relative">

                <!-- Barra de progreso de transición -->
                <div v-if="showTransition" class="absolute top-0 left-0 w-full h-1 bg-white/5 overflow-hidden">
                    <div class="h-full bg-amber-600 animate-[shrink_5s_linear_forwards] origin-left"></div>
                </div>

                <!-- Info Cantante Actual -->
                <div class="flex items-center gap-8 flex-1">
                    <div class="flex flex-col">
                        <span
                            class="text-amber-500 text-[10px] font-black uppercase tracking-widest mb-1 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-red-600 rounded-full animate-pulse"></span>
                            En Escena
                        </span>
                        <h2 class="text-4xl font-black uppercase tracking-tight leading-none italic">
                            {{ currentSong?.customer_name }}
                        </h2>
                    </div>

                    <div class="h-10 w-px bg-white/10"></div>

                    <div class="flex flex-col min-w-16">
                        <span class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-1">Mesa</span>
                        <span class="text-3xl font-black leading-none text-white/90">{{
                            currentSong?.service_table?.identifier }}</span>
                    </div>

                    <div class="h-10 w-px bg-white/10"></div>

                    <div class="flex flex-col max-w-sm">
                        <span class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-1">Cantando</span>
                        <span class="text-xl font-bold italic truncate text-amber-100/70">"{{ currentSong?.song?.title
                        }}"</span>
                    </div>
                </div>

                <!-- Info Próximo -->
                <div v-if="nextSong"
                    class="flex items-center gap-5 bg-white/5 px-5 py-2.5 rounded-xl border border-white/5">
                    <div class="text-right">
                        <span
                            class="text-white/30 text-[9px] font-black uppercase tracking-widest block mb-0.5">Siguiente</span>
                        <span class="text-lg font-bold">{{ nextSong?.customer_name }}</span>
                    </div>
                    <div
                        class="w-9 h-9 rounded-full bg-amber-600/10 border border-amber-600/30 flex items-center justify-center">
                        <span class="text-amber-500 font-black text-xs">{{ nextSong?.service_table?.identifier }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BANNER INFERIOR EN MOVIMIENTO (TICKER) -->
        <div class="h-7 bg-amber-600/95 overflow-hidden flex items-center border-t border-amber-400/20">
            <div
                class="whitespace-nowrap flex animate-marquee text-black font-black uppercase italic tracking-widest text-xs">
                <span class="px-10">HAZ TU PEDIDO ESCANEANDO EL QR DE TU MESA</span>
                <span class="px-10">HAZ TU PEDIDO ESCANEANDO EL QR DE TU MESA</span>
                <span class="px-10">HAZ TU PEDIDO ESCANEANDO EL QR DE TU MESA</span>
                <span class="px-10">HAZ TU PEDIDO ESCANEANDO EL QR DE TU MESA</span>
                <span class="px-10">HAZ TU PEDIDO ESCANEANDO EL QR DE TU MESA</span>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes shrink {
    from {
        transform: scaleX(1);
    }

    to {
        transform: scaleX(0);
    }
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-20%);
    }

    /* Ajustado según el número de repeticiones para que sea infinito fluido */
}

.animate-marquee {
    animation: marquee 15s linear infinite;
}

/* Ocultar cursor en toda la app */
.cursor-none {
    cursor: none !important;
}

/* Evitar interacción con el iframe de YouTube */
#youtube-player-a iframe,
#youtube-player-b iframe {
    pointer-events: none;
}

@keyframes stageLights {

    0% {
        opacity: 0.1;
        transform: rotate(0deg);
    }

    50% {
        opacity: 0.6;
        transform: rotate(5deg);
    }

    100% {
        opacity: 0.1;
        transform: rotate(0deg);
    }

}

.stage-lights {
    background:
        radial-gradient(circle at 20% 50%, rgba(255, 200, 0, 0.25), transparent 40%),
        radial-gradient(circle at 80% 50%, rgba(255, 0, 150, 0.25), transparent 40%),
        radial-gradient(circle at 50% 20%, rgba(0, 200, 255, 0.25), transparent 40%);
    animation: stageLights 4s ease-in-out infinite;
}

@keyframes applause {
    0% {
        transform: scale(1)
    }

    50% {
        transform: scale(1.2)
    }

    100% {
        transform: scale(1)
    }
}

.applause {
    animation: applause 0.6s ease-in-out infinite alternate;
}
</style>