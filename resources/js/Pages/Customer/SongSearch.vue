<script setup>
import { ref, onMounted } from 'vue';
import { Search, Music, Youtube, PlusCircle, Check, Loader2, User2, Edit2, Mic2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    table: Object,
    brandSettings: Object
});

const searchQuery = ref('');
const results = ref({ local: [], youtube: [] });
const isSearching = ref(false);
const isSubmitting = ref(false); // Declarado para evitar errores
const customerName = ref('');
const showingNamePrompt = ref(false);
const tempName = ref('');
const showSuccess = ref(false);

let searchTimer = null;

// 1. Buscador con Debounce Nativo
const performSearch = () => {
    clearTimeout(searchTimer);

    if (searchQuery.value.length < 3) {
        results.value = { local: [], youtube: [] };
        return;
    }

    searchTimer = setTimeout(async () => {
        isSearching.value = true;
        try {
            const res = await fetch(route('customer.songs.search', {
                identifier: props.table.identifier,
                q: searchQuery.value
            }));
            results.value = await res.json();
        } catch (error) {
            console.error("Error en la búsqueda:", error);
        } finally {
            isSearching.value = false;
        }
    }, 600);
};

// 2. Gestión de Identidad (Nombre)
const openNameModal = () => {
    tempName.value = customerName.value;
    showingNamePrompt.value = true; // Corregido: era .value, no .ref
};

const confirmCustomerName = () => {
    if (tempName.value.trim().length >= 2) {
        customerName.value = tempName.value;
        // Guardamos el nombre. Si quieres que sea por mesa, usa: `customer_name_${props.table.identifier}`
        localStorage.setItem('customer_name', tempName.value);
        showingNamePrompt.value = false;
    }
};

// 3. Envío de canción
const selectSong = (item, isYoutube = false) => {
    if (isSubmitting.value) return;

    if (!customerName.value) {
        openNameModal();
        return;
    }

    const payload = isYoutube
        ? {
            youtube_id: item.id.videoId,
            youtube_title: item.snippet.title,
            title: item.snippet.title,
            artist: item.snippet.channelTitle,
            thumbnail_url: item.snippet.thumbnails.high
                ? item.snippet.thumbnails.high.url
                : item.snippet.thumbnails.default.url,
            customer_name: customerName.value
        }
        : {
            youtube_id: item.youtube_id,
            title: item.title,
            artist: item.artist,
            customer_name: customerName.value
        };

    router.post(route('customer.songs.store', { identifier: props.table.identifier }), payload, {
        preserveScroll: true,
        onBefore: () => {
            isSubmitting.value = true;
        },
        onSuccess: () => {
            showSuccess.value = true;

            setTimeout(() => {
                router.get(route('customer.menu', { identifier: props.table.identifier }));
            }, 2500);
        },
        onError: (errors) => {
            console.error('Error al pedir canción:', errors);
            isSubmitting.value = false;
        },
        onFinish: () => {
            if (!showSuccess.value) {
                isSubmitting.value = false;
            }
        }
    });
};

onMounted(() => {
    const savedName = localStorage.getItem('customer_name');
    if (savedName) {
        customerName.value = savedName;
    } else {
        showingNamePrompt.value = true;
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#090a0f] text-white p-5 pb-24">

        <div
            class="flex items-center justify-between mb-6 px-2 bg-white/5 p-3 rounded-2xl border border-white/10 transition-all">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/20 rounded-lg">
                    <User2 class="w-4 h-4 text-indigo-400" />
                </div>
                <div>
                    <p class="text-[8px] font-black uppercase tracking-[0.2em] text-gray-500 leading-none">Cantando como
                    </p>
                    <p class="text-xs font-bold uppercase text-white truncate max-w-[120px]">
                        {{ customerName || 'Identifícate' }}
                    </p>
                </div>
            </div>
            <button @click="openNameModal"
                class="flex items-center gap-2 px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-xl transition-colors border border-indigo-500/20">
                <Edit2 class="w-3 h-3 text-indigo-400" />
                <span class="text-[9px] font-black uppercase tracking-wider text-indigo-400">Editar</span>
            </button>
        </div>

        <div class="sticky top-0 z-40 bg-[#090a0f] pb-6">
            <div class="relative group">
                <Search
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 group-focus-within:text-indigo-500 transition-colors" />
                <input v-model="searchQuery" @input="performSearch" placeholder="Busca tu canción..."
                    class="w-full bg-[#12141c] border-white/5 border p-5 pl-12 focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-sm uppercase font-black tracking-widest placeholder:text-gray-700 outline-none"
                    :style="{ borderRadius: brandSettings.border_radius || '1rem' }" />

                <div v-if="isSearching" class="absolute right-4 top-1/2 -translate-y-1/2">
                    <Loader2 class="w-5 h-5 text-indigo-500 animate-spin" />
                </div>
            </div>
        </div>

        <div v-if="results.local.length > 0" class="space-y-4 mb-10">
            <h4 class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.4em] pl-2 flex items-center gap-2">
                <Music class="w-3 h-3" /> Catálogo del Local
            </h4>
            <div v-for="song in results.local" :key="song.id" @click="selectSong(song)"
                class="group p-5 bg-[#12141c]/80 border border-white/5 flex items-center justify-between active:scale-95 transition-all hover:border-indigo-500/30"
                :class="{ 'opacity-50 pointer-events-none': isSubmitting }"
                :style="{ borderRadius: brandSettings.border_radius || '1rem' }">
                <div class="min-w-0 flex-1">
                    <p class="font-black text-sm uppercase truncate">{{ song.title }}</p>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">{{ song.artist }}</p>
                </div>
                <div class="p-2 rounded-full bg-white/5 group-hover:bg-indigo-500/20 transition-colors">
                    <PlusCircle class="w-4 h-4 text-indigo-500" />
                </div>
            </div>
        </div>

        <div v-if="results.youtube.length > 0" class="space-y-4">
            <h4 class="text-[9px] font-black text-red-500 uppercase tracking-[0.4em] pl-2 flex items-center gap-2">
                <Youtube class="w-3 h-3" /> Novedades Globales
            </h4>
            <div v-for="video in results.youtube" :key="video.id.videoId" @click="selectSong(video, true)"
                class="flex gap-4 p-3 bg-[#12141c]/40 border border-white/5 active:scale-95 transition-all overflow-hidden"
                :class="{ 'opacity-50 pointer-events-none': isSubmitting }"
                :style="{ borderRadius: brandSettings.border_radius }">

                <div class="relative shrink-0">
                    <img :src="video.snippet.thumbnails.default.url"
                        class="w-24 h-16 object-cover rounded-lg shadow-2xl shadow-black" />
                    <div
                        class="absolute inset-0 bg-red-500/20 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                        <Youtube class="w-6 h-6 text-white" />
                    </div>
                </div>

                <div class="flex-1 min-w-0 py-1">
                    <p class="text-[11px] font-black uppercase truncate text-white leading-tight">{{ video.snippet.title
                        }}</p>
                    <p class="text-[9px] font-bold text-gray-500 uppercase mt-1">{{ video.snippet.channelTitle }}</p>
                    <div class="inline-block mt-2 px-2 py-0.5 rounded-full bg-red-500/10 border border-red-500/20">
                        <p class="text-[7px] font-black text-red-500 uppercase tracking-widest">Pedir ahora</p>
                    </div>
                </div>
            </div>
        </div>

        <Transition name="fade">
            <div v-if="showingNamePrompt"
                class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-[#090a0f]/95 backdrop-blur-md">
                <div class="w-full max-w-xs space-y-8">
                    <div class="text-center space-y-2">
                        <div class="inline-flex p-4 rounded-full bg-indigo-500/10 mb-2">
                            <Mic2 class="w-8 h-8 text-indigo-500" />
                        </div>
                        <h2 class="text-2xl font-black uppercase tracking-tighter text-white">¿Quién canta?</h2>
                        <p class="text-gray-500 text-[9px] font-bold uppercase tracking-[0.3em]">Cambia el nombre si
                            compartes el móvil</p>
                    </div>

                    <div class="space-y-4">
                        <input v-model="tempName" @keyup.enter="confirmCustomerName" placeholder="Escribe tu apodo..."
                            class="w-full bg-[#12141c] border-white/10 border p-6 text-center text-lg font-black uppercase tracking-widest outline-none focus:border-indigo-500 transition-all text-white"
                            :style="{ borderRadius: brandSettings.border_radius }" />

                        <button @click="confirmCustomerName"
                            class="w-full py-5 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-2xl transition-all active:scale-95"
                            :style="{ backgroundColor: brandSettings.accent_color || '#6366f1', borderRadius: brandSettings.border_radius }">
                            Confirmar Identidad
                        </button>

                        <button v-if="customerName" @click="showingNamePrompt = false"
                            class="w-full text-[9px] font-bold text-gray-500 uppercase tracking-widest">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="isSubmitting && !showSuccess"
                class="fixed inset-0 z-[250] bg-[#090a0f]/90 backdrop-blur-sm flex items-center justify-center p-6 text-center">
                <div class="space-y-4">
                    <Loader2 class="w-10 h-10 text-indigo-500 animate-spin mx-auto" />
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white">
                        Enviando pedido...
                    </p>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showSuccess"
                class="fixed inset-0 z-[300] bg-[#090a0f] flex items-center justify-center p-6 text-center">
                <div class="space-y-6">
                    <div
                        class="relative mx-auto w-24 h-24 flex items-center justify-center bg-indigo-500 rounded-full shadow-[0_0_50px_rgba(99,102,241,0.5)] animate-bounce">
                        <Check class="w-12 h-12 text-white stroke-[4px]" />
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-3xl font-black uppercase tracking-tighter text-white">¡PEDIDO ENVIADO!</h2>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Prepárate para brillar en
                            el escenario</p>
                    </div>
                    <div class="pt-10">
                        <p class="text-[10px] text-indigo-400 font-black uppercase animate-pulse">Volviendo al menú
                            principal...</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>