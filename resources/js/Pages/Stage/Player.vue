<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue"
import axios from "axios"

const props = defineProps({
    current: Object,
    next: Object,
    settings: Object,
    ownerId: Number
})

const player = ref(null)

const currentSong = ref(props.current ?? null)
const nextSong = ref(props.next ?? null)

const playerReady = ref(false)
const highlightNext = ref(false)

const stageMode = ref("idle")
// idle | thanks | announce | countdown | waiting

const countdown = ref(3)
const transitionCurrentSong = ref(null)
const transitionNextSong = ref(null)

let progressTimer = null
let channel = null
let isTransitioning = false
let isLocalTransition = false
let countdownTimer = null
let phaseTimer = null

const CUT_BEFORE_END_SECONDS = 3
const THANKS_DURATION = 1800
const ANNOUNCE_DURATION = 1800

/*
|--------------------------------------------------------------------------
| COMPUTED
|--------------------------------------------------------------------------
*/

const channelOwnerId = computed(() => props.ownerId ?? props.settings?.user_id ?? null)
const isOverlayVisible = computed(() => ["thanks", "announce", "countdown", "waiting"].includes(stageMode.value))

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function clearProgressWatcher() {
    if (progressTimer) {
        clearInterval(progressTimer)
        progressTimer = null
    }
}

function clearPhaseTimer() {
    if (phaseTimer) {
        clearTimeout(phaseTimer)
        phaseTimer = null
    }
}

function clearCountdownTimer() {
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
}

function clearAllTimers() {
    clearProgressWatcher()
    clearPhaseTimer()
    clearCountdownTimer()
}

function stopPlayer() {
    try {
        if (player.value && typeof player.value.stopVideo === "function") {
            player.value.stopVideo()
        }
    } catch (error) {
        console.error("Error deteniendo reproductor:", error)
    }
}

function loadCurrentVideo() {
    if (!player.value) return
    if (!currentSong.value?.song?.youtube_id) return

    try {
        player.value.loadVideoById({
            videoId: currentSong.value.song.youtube_id,
            startSeconds: 0
        })
    } catch (error) {
        console.error("Error cargando video actual:", error)
    }
}

function triggerNextHighlight() {
    highlightNext.value = true

    setTimeout(() => {
        highlightNext.value = false
    }, 2200)
}

/*
|--------------------------------------------------------------------------
| YOUTUBE EVENTS
|--------------------------------------------------------------------------
*/

function startProgressWatcher() {
    clearProgressWatcher()

    progressTimer = setInterval(() => {
        if (!player.value) return
        if (typeof player.value.getDuration !== "function") return

        const duration = player.value.getDuration()
        const currentTime = player.value.getCurrentTime()

        if (!duration || currentTime == null) return

        const remaining = duration - currentTime

        if (
            remaining <= CUT_BEFORE_END_SECONDS &&
            remaining > 0 &&
            !isTransitioning &&
            stageMode.value === "idle"
        ) {
            startStageFlow()
        }
    }, 500)
}

function onPlayerReady() {
    playerReady.value = true

    if (!currentSong.value?.song?.youtube_id) {
        stageMode.value = "waiting"
    }
}

function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.PLAYING) {
        startProgressWatcher()
        stageMode.value = "idle"
    }

    if (event.data === YT.PlayerState.ENDED) {
        if (!isTransitioning) {
            startStageFlow()
        }
    }
}

function onPlayerError(event) {
    console.error("YouTube Player Error:", event.data, currentSong.value)

    if (!isTransitioning) {
        startStageFlow()
    }
}

/*
|--------------------------------------------------------------------------
| PLAYER
|--------------------------------------------------------------------------
*/

function createPlayer() {
    player.value = new YT.Player("youtube-player", {
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
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange,
            onError: onPlayerError
        }
    })
}

/*
|--------------------------------------------------------------------------
| BACKEND
|--------------------------------------------------------------------------
*/

async function finishSong() {
    if (!currentSong.value?.id) return false

    try {
        const { data } = await axios.post(`/stage/${currentSong.value.id}/finish`)

        if (!data?.success) return false

        currentSong.value = data.current ?? null
        nextSong.value = data.next ?? null

        return true
    } catch (error) {
        console.error("Error finalizando canción:", error)
        return false
    }
}

/*
|--------------------------------------------------------------------------
| STAGE FLOW
|--------------------------------------------------------------------------
*/

async function startStageFlow() {
    if (isTransitioning || !currentSong.value?.id) return

    isTransitioning = true
    isLocalTransition = true

    transitionCurrentSong.value = currentSong.value
    transitionNextSong.value = nextSong.value

    clearProgressWatcher()
    stopPlayer()

    // 1) agradecimiento al que cantó
    stageMode.value = "thanks"

    phaseTimer = setTimeout(async () => {
        const ok = await finishSong()

        // actualizamos el siguiente a anunciar con el valor ya más reciente
        if (nextSong.value) {
            transitionNextSong.value = currentSong.value
        }

        // Si después de terminar no hay nuevo current, no hay siguiente.
        if (!ok || !currentSong.value?.song?.youtube_id) {
            stageMode.value = "waiting"
            isTransitioning = false
            isLocalTransition = false
            return
        }

        // 2) anuncio del siguiente
        stageMode.value = "announce"

        phaseTimer = setTimeout(() => {
            // 3) conteo regresivo
            stageMode.value = "countdown"
            countdown.value = 3

            clearCountdownTimer()
            countdownTimer = setInterval(() => {
                countdown.value--

                if (countdown.value <= 0) {
                    clearCountdownTimer()

                    loadCurrentVideo()

                    setTimeout(() => {
                        stageMode.value = "idle"
                        transitionCurrentSong.value = null
                        transitionNextSong.value = null
                        isTransitioning = false
                        isLocalTransition = false
                    }, 250)
                }
            }, 900)
        }, ANNOUNCE_DURATION)
    }, THANKS_DURATION)
}

/*
|--------------------------------------------------------------------------
| REALTIME
|--------------------------------------------------------------------------
*/

function initRealtime() {
    if (!window.Echo) {
        console.error("Echo no disponible")
        return
    }

    if (!channelOwnerId.value) {
        console.error("ownerId no disponible para realtime")
        return
    }

    channel = window.Echo.private(`karaoke.${channelOwnerId.value}`)

    channel.listen(".queue.updated", (event) => {
        console.log("Realtime QueueUpdated:", event)

        const previousNextId = nextSong.value?.id ?? null

        const playing = event.fullQueue.find(q => q.status === "playing") ?? null
        const next = event.fullQueue.find(
            q => q.status === "pending" || q.status === "ready"
        ) ?? null

        const currentVideo = currentSong.value?.song?.youtube_id ?? null
        const newVideo = playing?.song?.youtube_id ?? null

        currentSong.value = playing
        nextSong.value = next

        // si acaba de entrar un nuevo "siguiente", resaltamos
        if (next?.id && next.id !== previousNextId && !isOverlayVisible.value) {
            triggerNextHighlight()
        }

        // Si estamos en espera y entra una nueva canción como playing, que arranque sola
        if (
            stageMode.value === "waiting" &&
            newVideo &&
            newVideo !== currentVideo &&
            playerReady.value
        ) {
            loadCurrentVideo()
            stageMode.value = "idle"
            return
        }

        // si esta misma pantalla está haciendo la transición, no duplicamos
        if (isLocalTransition) return

        // si desde el panel cambiaron manualmente la canción actual
        if (
            newVideo &&
            newVideo !== currentVideo &&
            !isOverlayVisible.value &&
            playerReady.value
        ) {
            stopPlayer()
            loadCurrentVideo()
        }
    })
}

/*
|--------------------------------------------------------------------------
| YOUTUBE API
|--------------------------------------------------------------------------
*/

function initYouTube() {
    createPlayer()
}

function loadYouTubeAPI() {
    if (window.YT && window.YT.Player) {
        initYouTube()
        return
    }

    const existing = document.querySelector(
        'script[src="https://www.youtube.com/iframe_api"]'
    )

    if (!existing) {
        const tag = document.createElement("script")
        tag.src = "https://www.youtube.com/iframe_api"
        document.body.appendChild(tag)
    }

    window.onYouTubeIframeAPIReady = initYouTube
}

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/

watch(
    () => props.current,
    (value) => {
        currentSong.value = value ?? null
    }
)

watch(
    () => props.next,
    (value) => {
        nextSong.value = value ?? null
    }
)

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
    clearAllTimers()

    try {
        if (player.value) {
            player.value.destroy()
        }
    } catch (error) {
        console.error("Error destruyendo player:", error)
    }

    if (window.Echo && channelOwnerId.value) {
        window.Echo.leave(`karaoke.${channelOwnerId.value}`)
    }
})
</script>

<template>
    <div class="fixed inset-0 bg-black flex flex-col overflow-hidden font-sans select-none cursor-none text-white">

        <!-- VIDEO -->
        <div class="relative flex-grow bg-black overflow-hidden">
            <div id="youtube-player" class="absolute inset-0 z-10"></div>

            <!-- OVERLAY STAGE -->
            <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-400 ease-in"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="isOverlayVisible"
                    class="absolute inset-0 z-[9999] flex items-center justify-center bg-black/95 backdrop-blur-sm overflow-hidden">
                    <div class="absolute inset-0 stage-lights"></div>

                    <!-- AGRADECIMIENTO -->
                    <div v-if="stageMode === 'thanks'" class="relative text-center z-10 max-w-5xl px-8">
                        <p class="text-amber-400 text-lg font-black uppercase tracking-[0.35em] mb-6 animate-pulse">
                            ¡Gracias por cantar!
                        </p>

                        <h1
                            class="text-[120px] font-black uppercase tracking-tight text-white drop-shadow-[0_0_60px_rgba(255,255,255,0.9)]">
                            {{ transitionCurrentSong?.customer_name }}
                        </h1>

                        <div class="mt-5 text-3xl text-white/90 italic tracking-wide">
                            Mesa {{ transitionCurrentSong?.service_table?.identifier }}
                        </div>

                        <div class="mt-5 text-2xl text-white/75 italic">
                            "{{ transitionCurrentSong?.song?.title }}"
                        </div>

                        <div class="mt-10 text-6xl applause">👏 👏 👏</div>
                    </div>

                    <!-- ANUNCIO DEL SIGUIENTE -->
                    <div v-else-if="stageMode === 'announce'" class="relative text-center z-10 max-w-5xl px-8">
                        <p class="text-amber-400 text-xl font-black uppercase tracking-[0.35em] mb-8 animate-pulse">
                            Siguiente en el Escenario
                        </p>

                        <h1
                            class="text-[140px] font-black uppercase tracking-tight text-white drop-shadow-[0_0_60px_rgba(255,255,255,0.9)]">
                            {{ currentSong?.customer_name }}
                        </h1>

                        <div class="mt-6 text-4xl text-white/90 italic tracking-wide">
                            Mesa {{ currentSong?.service_table?.identifier }}
                        </div>

                        <div class="mt-6 text-3xl text-white/80 italic">
                            "{{ currentSong?.song?.title }}"
                        </div>

                        <div class="mt-12 text-6xl applause">👏 👏 👏</div>
                    </div>

                    <!-- CONTEO -->
                    <div v-else-if="stageMode === 'countdown'" class="relative text-center z-10 max-w-5xl px-8">
                        <p class="text-amber-400 text-lg font-black uppercase tracking-[0.35em] mb-8">
                            Preparados...
                        </p>

                        <h1
                            class="text-[180px] leading-none font-black text-white drop-shadow-[0_0_80px_rgba(255,255,255,0.9)] countdown-pop">
                            {{ countdown }}
                        </h1>

                        <div class="mt-6 text-3xl font-black uppercase text-white">
                            {{ currentSong?.customer_name }}
                        </div>

                        <div class="mt-3 text-xl text-white/70 italic">
                            "{{ currentSong?.song?.title }}"
                        </div>
                    </div>

                    <!-- ESPERA -->
                    <div v-else-if="stageMode === 'waiting'" class="relative text-center z-10 max-w-5xl px-8">
                        <p class="text-amber-400 text-xl font-black uppercase tracking-[0.35em] mb-8 animate-pulse">
                            Escenario libre
                        </p>

                        <h1 class="text-[90px] font-black uppercase tracking-tight text-white/95">
                            Esperando próximo cantante
                        </h1>

                        <div class="mt-8 text-2xl text-white/60 italic">
                            Escanea el QR de tu mesa y pide tu canción
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- FOOTER -->
        <div v-if="!isOverlayVisible" class="flex flex-col z-50 shadow-[0_-10px_40px_rgba(0,0,0,0.8)]">
            <div class="h-28 bg-[#0a0a0a] border-t border-white/5 flex items-center px-10 relative">
                <div class="flex items-center gap-8 flex-1">
                    <div class="flex flex-col">
                        <span
                            class="text-amber-500 text-[10px] font-black uppercase tracking-widest mb-1 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-red-600 rounded-full animate-pulse"></span>
                            En Escena
                        </span>
                        <h2 class="text-4xl font-black uppercase tracking-tight leading-none italic">
                            {{ currentSong?.customer_name || "—" }}
                        </h2>
                    </div>

                    <div class="h-10 w-px bg-white/10"></div>

                    <div class="flex flex-col min-w-16">
                        <span class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-1">Mesa</span>
                        <span class="text-3xl font-black leading-none text-white/90">
                            {{ currentSong?.service_table?.identifier || "—" }}
                        </span>
                    </div>

                    <div class="h-10 w-px bg-white/10"></div>

                    <div class="flex flex-col max-w-sm">
                        <span class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-1">Cantando</span>
                        <span class="text-xl font-bold italic truncate text-amber-100/70">
                            "{{ currentSong?.song?.title || "Sin título" }}"
                        </span>
                    </div>
                </div>

                <Transition enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-x-4 scale-95"
                    enter-to-class="opacity-100 translate-x-0 scale-100"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="opacity-100 translate-x-0 scale-100"
                    leave-to-class="opacity-0 translate-x-4 scale-95">
                    <div v-if="nextSong" :key="nextSong.id"
                        class="flex items-center gap-5 bg-white/5 px-5 py-2.5 rounded-xl border border-white/5 transition-all duration-500"
                        :class="highlightNext ? 'ring-2 ring-amber-400/70 bg-amber-500/10 shadow-[0_0_25px_rgba(251,191,36,0.25)] scale-[1.03]' : ''">
                        <div class="text-right">
                            <span class="text-white/30 text-[9px] font-black uppercase tracking-widest block mb-0.5">
                                Siguiente
                            </span>
                            <span class="text-lg font-bold">{{ nextSong?.customer_name }}</span>
                            <div class="text-[10px] text-white/55 italic mt-0.5">
                                "{{ nextSong?.song?.title }}"
                            </div>
                        </div>

                        <div
                            class="w-9 h-9 rounded-full bg-amber-600/10 border border-amber-600/30 flex items-center justify-center">
                            <span class="text-amber-500 font-black text-xs">
                                {{ nextSong?.service_table?.identifier }}
                            </span>
                        </div>
                    </div>
                </Transition>

                <div v-if="!nextSong"
                    class="flex items-center gap-5 bg-white/5 px-5 py-2.5 rounded-xl border border-white/5">
                    <div class="text-right">
                        <span class="text-white/30 text-[9px] font-black uppercase tracking-widest block mb-0.5">
                            Siguiente
                        </span>
                        <span class="text-lg font-bold text-white/40">Esperando pedido...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TICKER -->
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
@keyframes marquee {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-20%);
    }
}

.animate-marquee {
    animation: marquee 15s linear infinite;
}

.cursor-none {
    cursor: none !important;
}

#youtube-player iframe {
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
        transform: scale(1);
    }

    50% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

.applause {
    animation: applause 0.6s ease-in-out infinite alternate;
}

@keyframes countdownPop {
    0% {
        transform: scale(0.7);
        opacity: 0.2;
    }

    50% {
        transform: scale(1.08);
        opacity: 1;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.countdown-pop {
    animation: countdownPop 0.8s ease-out;
}
</style>