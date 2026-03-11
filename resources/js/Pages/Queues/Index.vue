<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Play, CheckCircle2, Star, Music, User2, Mic2, Flame } from 'lucide-vue-next'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    queues: Array
})

const page = usePage()

const settings = computed(() => page.props?.auth?.settings ?? {})
const userId = computed(() => page.props?.auth?.user?.id ?? null)

const stageWindow = ref(null)
const queues = ref([...props.queues])

let channel = null

/*
|--------------------------------------------------------------------------
| REALTIME LISTENER
|--------------------------------------------------------------------------
*/

onMounted(() => {

    if (!window.Echo || !userId.value) {
        console.warn("Echo o userId no disponible")
        return
    }

    channel = window.Echo.private(`karaoke.${userId.value}`)

    channel.listen('.queue.updated', (e) => {

        console.log("Realtime QueueUpdated:", e)

        if (!e.fullQueue) return

        // fuerza reactividad profunda
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

                if (!stageWindow.value || stageWindow.value.closed) {

                    stageWindow.value = window.open(
                        `/stage/${userId.value}`,
                        'karaoke_stage',
                        'width=1280,height=720'
                    )

                } else {

                    stageWindow.value.focus()

                }

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
        { preserveScroll: true }
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

            <div class="flex items-end justify-between px-2">
                <div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Cola de Reproducción</h2>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.3em] mt-1">
                        {{ queues.length }} Pedidos en el sistema
                    </p>
                </div>
                <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/5 rounded-full">
                    <Flame class="w-3 h-3 text-orange-500 animate-pulse" />
                    <span class="text-[9px] font-black text-white/50 uppercase tracking-widest">Show en Vivo</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div v-for="(item, index) in queues" :key="item.id"
                    class="group relative overflow-hidden transition-all duration-500 border backdrop-blur-md" :style="{
                        backgroundColor: item.status === 'playing' ? `${settings?.accent_color || '#6366f1'}15` : '#12141c80',
                        borderColor: item.status === 'playing' ? `${settings?.accent_color || '#6366f1'}40` : 'rgba(255,255,255,0.05)',
                        borderRadius: settings?.border_radius || '1.25rem'
                    }">

                    <div v-if="item.status === 'playing'" class="absolute left-0 top-0 bottom-0 w-1"
                        :style="{ backgroundColor: settings?.accent_color || '#6366f1' }">
                    </div>

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
                                        color: item.status === 'playing' ? (settings?.accent_color || '#6366f1') : '#ffffff',
                                        opacity: item.status === 'playing' ? '1' : '0.05'
                                    }">
                                    {{ (index + 1).toString().padStart(2, '0') }}
                                </span>
                            </div>

                            <div class="space-y-1 flex-1">
                                <div class="flex items-center gap-3">
                                    <h3
                                        class="text-xl md:text-2xl font-black text-white uppercase tracking-tight group-hover:tracking-normal transition-all duration-500">
                                        {{ item.song?.title || 'Petición de Mesa' }}
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
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-600">No hay voces en espera
                    </p>
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