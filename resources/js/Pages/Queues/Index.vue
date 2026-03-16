<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import {
    Play,
    CheckCircle2,
    Star,
    Music,
    User2,
    Mic2,
    Flame,
    MonitorPlay,
    History,
    ListMusic,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    queues: {
        type: Array,
        default: () => []
    },
    history: {
        type: Object,
        default: () => ({
            data: [],
            links: []
        })
    }
})

const page = usePage()

const settings = computed(() => page.props?.auth?.settings ?? {})
const userId = computed(() => page.props?.auth?.user?.id ?? null)

const activeTab = ref('queue')
const stageWindow = ref(null)
const queues = ref([...props.queues])

let channel = null

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const historyItems = computed(() => props.history?.data ?? [])
const historyLinks = computed(() => props.history?.links ?? [])

const formatSongTitle = (item) => {
    return item?.song?.title || item?.song?.youtube_title || 'Sin título'
}

const formatPlayedAt = (item) => {
    if (!item?.updated_at) return 'Sin fecha'

    return new Date(item.updated_at).toLocaleString('es-PE', {
        dateStyle: 'short',
        timeStyle: 'short'
    })
}

/*
|--------------------------------------------------------------------------
| REALTIME LISTENER
|--------------------------------------------------------------------------
*/

onMounted(() => {
    if (!window.Echo || !userId.value) {
        console.warn('Echo o userId no disponible')
        return
    }

    channel = window.Echo.private(`karaoke.${userId.value}`)

    channel.listen('.queue.updated', (e) => {
        console.log('Realtime QueueUpdated:', e)

        if (!e.fullQueue) return

        queues.value = [...e.fullQueue]
    })
})

onBeforeUnmount(() => {
    if (window.Echo && userId.value) {
        window.Echo.leave(`karaoke.${userId.value}`)
    }
})

/*
|--------------------------------------------------------------------------
| OPEN STAGE
|--------------------------------------------------------------------------
*/

const openStage = () => {
    if (!userId.value) return

    const stageUrl = route('stage.show', userId.value)

    if (!stageWindow.value || stageWindow.value.closed) {
        stageWindow.value = window.open(
            stageUrl,
            'karaoke_stage',
            'width=1280,height=720'
        )
    } else {
        stageWindow.value.focus()
    }
}

/*
|--------------------------------------------------------------------------
| PLAY SONG
|--------------------------------------------------------------------------
*/

const playSong = (id) => {
    router.patch(
        route('queues.update-status', id),
        { status: 'playing' },
        {
            preserveScroll: true,
            onSuccess: () => {
                openStage()
            }
        }
    )
}

/*
|--------------------------------------------------------------------------
| FINISH SONG
|--------------------------------------------------------------------------
*/

const finishSong = (id) => {
    router.patch(
        route('queues.update-status', id),
        { status: 'played' },
        {
            preserveScroll: true
        }
    )
}

/*
|--------------------------------------------------------------------------
| HISTORY PAGINATION
|--------------------------------------------------------------------------
*/

const goToHistoryPage = (url) => {
    if (!url) return

    router.get(
        url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['history']
        }
    )
}
</script>

<template>

    <Head title="Control de Cola" />

    <AuthenticatedLayout :style="{ fontFamily: settings?.font_family || 'Inter' }">
        <template #header>
            <span>Gestión de Peticiones</span>
        </template>

        <div id="scroll-anchor" class="relative -top-24"></div>

        <div class="max-w-6xl mx-auto pb-20 px-4 space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 px-2">
                <div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter">
                        Gestión de Karaoke
                    </h2>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.3em] mt-1">
                        {{ activeTab === 'queue'
                            ? `${queues.length} pedidos activos`
                            : `${historyItems.length} registros en esta página` }}
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <button type="button" @click="openStage"
                        class="flex items-center justify-center gap-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-white border border-white/5 bg-white/5 hover:bg-white/10 transition-all active:scale-95"
                        :style="{ borderRadius: settings?.border_radius || '0.75rem' }">
                        <MonitorPlay class="w-4 h-4" />
                        Abrir Escenario
                    </button>

                    <div
                        class="hidden md:flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/5 rounded-full">
                        <Flame class="w-3 h-3 text-orange-500 animate-pulse" />
                        <span class="text-[9px] font-black text-white/50 uppercase tracking-widest">
                            Show en Vivo
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABS -->
            <div class="p-2 bg-[#0f1117]/80 border border-white/5 flex items-center gap-2 w-fit"
                :style="{ borderRadius: settings?.border_radius || '1rem' }">
                <button type="button" @click="activeTab = 'queue'"
                    class="flex items-center gap-2 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] transition-all"
                    :style="{
                        borderRadius: '0.75rem',
                        backgroundColor: activeTab === 'queue'
                            ? (settings?.accent_color || '#6366f1')
                            : 'transparent',
                        color: activeTab === 'queue' ? '#ffffff' : 'rgba(255,255,255,0.55)'
                    }">
                    <ListMusic class="w-4 h-4" />
                    Cola
                </button>

                <button type="button" @click="activeTab = 'history'"
                    class="flex items-center gap-2 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] transition-all"
                    :style="{
                        borderRadius: '0.75rem',
                        backgroundColor: activeTab === 'history'
                            ? (settings?.accent_color || '#6366f1')
                            : 'transparent',
                        color: activeTab === 'history' ? '#ffffff' : 'rgba(255,255,255,0.55)'
                    }">
                    <History class="w-4 h-4" />
                    Historial
                </button>
            </div>

            <!-- TAB: COLA -->
            <div v-if="activeTab === 'queue'" class="grid grid-cols-1 gap-4">
                <div v-for="(item, index) in queues" :key="item.id"
                    class="group relative overflow-hidden transition-all duration-500 border backdrop-blur-md" :style="{
                        backgroundColor: item.status === 'playing'
                            ? `${settings?.accent_color || '#6366f1'}15`
                            : '#12141c80',
                        borderColor: item.status === 'playing'
                            ? `${settings?.accent_color || '#6366f1'}40`
                            : 'rgba(255,255,255,0.05)',
                        borderRadius: settings?.border_radius || '1.25rem'
                    }">
                    <div v-if="item.status === 'playing'" class="absolute left-0 top-0 bottom-0 w-1"
                        :style="{ backgroundColor: settings?.accent_color || '#6366f1' }"></div>

                    <div v-if="item.is_vip || item.amount_paid > 0"
                        class="absolute top-0 right-12 px-4 py-1 bg-yellow-500 text-black text-[9px] font-black uppercase tracking-[0.2em] rounded-b-xl shadow-lg shadow-yellow-500/20">
                        <Star class="w-3 h-3 inline-block mr-1 fill-black" />
                        {{ item.amount_paid > 0 ? `S/ ${item.amount_paid}` : 'Prioridad' }}
                    </div>

                    <div class="p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-8 w-full">
                            <div class="relative flex flex-col items-center justify-center min-w-[3.5rem]">
                                <span
                                    class="text-5xl font-black italic tracking-tighter transition-opacity duration-700"
                                    :style="{
                                        color: item.status === 'playing'
                                            ? (settings?.accent_color || '#6366f1')
                                            : '#ffffff',
                                        opacity: item.status === 'playing' ? '1' : '0.05'
                                    }">
                                    {{ (index + 1).toString().padStart(2, '0') }}
                                </span>
                            </div>

                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex items-center gap-3">
                                    <h3
                                        class="text-xl md:text-2xl font-black text-white uppercase tracking-tight group-hover:tracking-normal transition-all duration-500 truncate">
                                        {{ formatSongTitle(item) }}
                                    </h3>

                                    <div v-if="item.status === 'playing'" class="flex gap-1">
                                        <div v-for="i in 3" :key="i" class="w-1 bg-indigo-500 animate-bounce"
                                            :style="{ animationDelay: i * 0.1 + 's', height: '12px' }"></div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-4">
                                    <span
                                        class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em]"
                                        :style="{ color: settings?.accent_color || '#6366f1' }">
                                        <Mic2 class="w-3 h-3" />
                                        {{ item.service_table?.identifier || 'Mesa X' }}
                                    </span>

                                    <span class="w-1 h-1 rounded-full bg-white/10"></span>

                                    <span
                                        class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
                                        <User2 class="w-3 h-3" />
                                        {{ item.customer_name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 w-full md:w-auto border-t md:border-t-0 border-white/5 pt-4 md:pt-0">
                            <button v-if="item.status === 'pending'" @click="playSong(item.id)"
                                class="flex-1 md:flex-none flex items-center justify-center gap-3 bg-white/5 hover:bg-white/10 text-white font-bold uppercase tracking-[0.2em] px-8 py-4 text-[10px] border border-white/5 transition-all active:scale-95"
                                :style="{ borderRadius: settings?.border_radius || '0.75rem' }">
                                <Play class="w-4 h-4 fill-current" />
                                Reproducir
                            </button>

                            <button v-if="item.status === 'playing'" @click="finishSong(item.id)"
                                class="flex-1 md:flex-none flex items-center justify-center gap-3 text-white font-bold uppercase tracking-[0.2em] px-8 py-4 text-[10px] shadow-xl transition-all active:scale-95"
                                :style="{
                                    backgroundColor: settings?.accent_color || '#6366f1',
                                    borderRadius: settings?.border_radius || '0.75rem',
                                    boxShadow: `0 10px 30px -10px ${settings?.accent_color || '#6366f1'}60`
                                }">
                                <CheckCircle2 class="w-4 h-4" />
                                Finalizar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="queues.length === 0"
                    class="p-20 bg-[#12141c]/30 border border-dashed border-white/5 flex flex-col items-center justify-center space-y-4"
                    :style="{ borderRadius: settings?.border_radius || '2rem' }">
                    <div class="p-6 bg-white/5 rounded-full border border-white/5">
                        <Music class="w-8 h-8 text-white/10" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-600">
                        No hay voces en espera
                    </p>
                </div>
            </div>

            <!-- TAB: HISTORIAL -->
            <div v-if="activeTab === 'history'" class="space-y-4">
                <div v-for="item in historyItems" :key="`history-${item.id}`"
                    class="group relative overflow-hidden transition-all duration-500 border backdrop-blur-md bg-[#0f1117]/80 border-white/5"
                    :style="{ borderRadius: settings?.border_radius || '1.25rem' }">
                    <div class="p-5 flex flex-col md:flex-row items-center justify-between gap-5">
                        <div class="flex items-center gap-6 w-full">
                            <div
                                class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/5 border border-white/5 shrink-0">
                                <CheckCircle2 class="w-5 h-5 text-emerald-400" />
                            </div>

                            <div class="space-y-1 flex-1 min-w-0">
                                <h3 class="text-lg md:text-xl font-black text-white uppercase tracking-tight truncate">
                                    {{ formatSongTitle(item) }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-4">
                                    <span
                                        class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em]"
                                        :style="{ color: settings?.accent_color || '#6366f1' }">
                                        <Mic2 class="w-3 h-3" />
                                        {{ item.service_table?.identifier || 'Mesa X' }}
                                    </span>

                                    <span class="w-1 h-1 rounded-full bg-white/10"></span>

                                    <span
                                        class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
                                        <User2 class="w-3 h-3" />
                                        {{ item.customer_name }}
                                    </span>

                                    <span class="w-1 h-1 rounded-full bg-white/10"></span>

                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">
                                        {{ formatPlayedAt(item) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-4 py-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            Reproducida
                        </div>
                    </div>
                </div>

                <div v-if="historyItems.length === 0"
                    class="p-20 bg-[#12141c]/30 border border-dashed border-white/5 flex flex-col items-center justify-center space-y-4"
                    :style="{ borderRadius: settings?.border_radius || '2rem' }">
                    <div class="p-6 bg-white/5 rounded-full border border-white/5">
                        <History class="w-8 h-8 text-white/10" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-600">
                        Aún no hay historial
                    </p>
                </div>

                <!-- PAGINACIÓN -->
                <div v-if="historyLinks.length > 3" class="flex items-center justify-center gap-2 pt-4 flex-wrap">
                    <button v-for="(link, index) in historyLinks" :key="index" type="button"
                        @click="goToHistoryPage(link.url)" :disabled="!link.url || link.active"
                        class="min-w-[42px] px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] border transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                        :style="{
                            borderRadius: '0.75rem',
                            backgroundColor: link.active
                                ? (settings?.accent_color || '#6366f1')
                                : 'rgba(255,255,255,0.04)',
                            borderColor: 'rgba(255,255,255,0.06)',
                            color: '#ffffff'
                        }" v-html="link.label.includes('Previous')
                            ? '<'
                            : link.label.includes('Next')
                                ? '>'
                                : link.label
                            " />
                </div>
            </div>

            <div class="flex items-center justify-between px-2 pt-10">
                <div class="flex items-center gap-4">
                    <div class="h-[1px] w-8 bg-white/5"></div>
                    <p class="text-[8px] text-gray-700 font-bold uppercase tracking-[0.4em]">
                        Live Queue Control // {{ settings?.local_name || 'Cantalope' }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
* {
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
}

.max-w-6xl {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>