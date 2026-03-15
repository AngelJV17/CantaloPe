<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';
import {
    Youtube,
    Plus,
    Trash2,
    Music,
    BarChart3,
    Loader2,
    User,
    Pencil,
    X
} from 'lucide-vue-next';

const props = defineProps({
    songs: Object,
    filters: Object,
    settings: Object,
});

const page = usePage();

/*
|--------------------------------------------------------------------------
| TOASTS
|--------------------------------------------------------------------------
*/

watch(() => page.props.flash, (flash) => {
    if (!flash) return;

    const successMsg = flash.success || flash.message;
    const errorMsg = flash.error;

    if (!successMsg && !errorMsg) return;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: props.settings?.sidebar_color || '#12141c',
        color: '#ffffff',
        customClass: {
            popup: 'border border-white/10 backdrop-blur-xl rounded-2xl shadow-2xl',
        }
    });

    if (successMsg) {
        Toast.fire({
            icon: 'success',
            title: String(successMsg).toUpperCase(),
            iconColor: props.settings?.accent_color || '#6366f1'
        });
    }

    if (errorMsg) {
        Toast.fire({
            icon: 'error',
            title: String(errorMsg).toUpperCase()
        });
    }
}, { deep: true, immediate: true });

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

let timer = null;

watch(search, (v) => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        router.get(
            route('songs.index'),
            { search: v, perPage: perPage.value },
            { preserveState: true, replace: true, preserveScroll: true }
        );
    }, 400);
});

watch(perPage, (v) => {
    router.get(
        route('songs.index'),
        { search: search.value, perPage: v },
        { preserveState: true, preserveScroll: true }
    );
});

/*
|--------------------------------------------------------------------------
| THEME
|--------------------------------------------------------------------------
*/

const themeStyles = computed(() => ({
    '--bg-card': props.settings?.sidebar_color || '#12141c',
    '--accent': props.settings?.accent_color || '#6366f1',
    '--radius': props.settings?.border_radius || '1.25rem',
    fontFamily: props.settings?.font_family || 'Inter',
}));

/*
|--------------------------------------------------------------------------
| CREATE SONG
|--------------------------------------------------------------------------
*/

const form = useForm({
    youtube_url: ''
});

const submit = () => {
    form.post(route('songs.store'), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true
    });
};

/*
|--------------------------------------------------------------------------
| EDIT SONG MODAL
|--------------------------------------------------------------------------
*/

const showEditModal = ref(false);
const editingSong = ref(null);

const editForm = useForm({
    title: '',
    artist: '',
});

const openEditModal = (song) => {
    editingSong.value = song;

    editForm.reset();
    editForm.clearErrors();

    editForm.title = song.title || '';
    editForm.artist = song.artist || '';

    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingSong.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const updateSong = () => {
    if (!editingSong.value) return;

    editForm.put(route('songs.update', editingSong.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        }
    });
};

/*
|--------------------------------------------------------------------------
| DELETE SONG
|--------------------------------------------------------------------------
*/

const deleteFromLibrary = (id) => {
    Swal.fire({
        title: '¿ELIMINAR HIT?',
        text: 'ESTA ACCIÓN NO SE PUEDE DESHACER',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR',
        background: props.settings?.sidebar_color || '#12141c',
        color: '#fff',
        customClass: {
            popup: `rounded-[${props.settings?.border_radius || '1rem'}] border border-white/10 backdrop-blur-xl shadow-2xl`,
            title: 'font-black uppercase italic tracking-widest text-xl',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('songs.destroy', id), {
                preserveScroll: true
            });
        }
    });
};
</script>

<template>

    <Head title="Biblioteca" />

    <AuthenticatedLayout>
        <template #header>
            <span>Biblioteca Maestra</span>
        </template>

        <div class="max-w-7xl mx-auto pb-20 px-4 mt-8 space-y-6" :style="themeStyles">

            <!-- FORM REGISTRAR -->
            <div
                class="p-8 bg-[var(--bg-card)]/50 border border-white/5 backdrop-blur-xl shadow-2xl rounded-[var(--radius)]">
                <div class="mb-6">
                    <h2 class="text-xl font-black text-white uppercase italic tracking-widest">
                        Alimentar <span class="text-[var(--accent)]">Sistema</span>
                    </h2>
                </div>

                <form @submit.prevent="submit"
                    class="flex gap-3 p-2 bg-black/40 border border-white/5 rounded-2xl focus-within:border-[var(--accent)]/50 transition-all shadow-inner">
                    <div class="flex-1 flex items-center px-4 gap-3">
                        <Youtube class="w-6 h-6 text-red-600 shadow-[0_0_15px_rgba(220,38,38,0.4)]" />
                        <input v-model="form.youtube_url" type="text" placeholder="PEGA EL LINK DE YOUTUBE AQUÍ..."
                            class="w-full bg-transparent border-none text-white font-bold text-xs uppercase focus:ring-0" />
                    </div>

                    <PrimaryButton :disabled="form.processing" :style="{ borderRadius: '0.75rem' }">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                        <Plus v-else class="w-4 h-4 mr-2" />
                        REGISTRAR
                    </PrimaryButton>
                </form>

                <InputError :message="form.errors.youtube_url"
                    class="mt-2 ml-4 text-[10px] uppercase italic text-red-500" />
            </div>

            <!-- TABLA -->
            <div
                class="bg-[var(--bg-card)]/30 border border-white/5 backdrop-blur-xl shadow-2xl rounded-[var(--radius)] overflow-hidden">
                <div class="p-6 border-b border-white/5 bg-black/20 flex flex-col gap-5">
                    <div class="flex items-center gap-3">
                        <Music class="w-4 h-4 text-[var(--accent)]" />
                        <h3 class="font-black text-white uppercase text-xs tracking-[0.3em]">
                            Hits Registrados
                        </h3>
                    </div>

                    <TableFilters v-model:search="search" v-model:perPage="perPage"
                        placeholder="BUSCAR CANCIÓN, ARTISTA O ID..." />
                </div>

                <div class="overflow-x-auto">
                    <table v-if="songs.data.length > 0" class="w-full text-left">
                        <thead>
                            <tr
                                class="text-xs font-black text-gray-500 uppercase tracking-[0.2em] border-b border-white/5 bg-black/40">
                                <th class="px-8 py-6 w-20">#</th>
                                <th class="px-4 py-6 text-center">Vista</th>
                                <th class="px-8 py-6">Información del Hit</th>
                                <th class="px-8 py-6 text-center">Metadata</th>
                                <th class="px-8 py-6 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/5">
                            <tr v-for="(song, index) in songs.data" :key="song.id"
                                class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-4 text-[10px] font-mono text-gray-500 italic">
                                    {{ (songs.current_page - 1) * songs.per_page + index + 1 }}
                                </td>

                                <td class="px-4 py-4">
                                    <div
                                        class="relative w-24 h-14 rounded-xl overflow-hidden border border-white/10 group-hover:border-[var(--accent)]/50 transition-all shadow-lg mx-auto">
                                        <img :src="song.thumbnail_url || `https://img.youtube.com/vi/${song.youtube_id}/mqdefault.jpg`"
                                            class="w-full h-full object-cover" />
                                    </div>
                                </td>

                                <td class="px-8 py-4">
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-sm font-black text-white uppercase tracking-tight truncate max-w-xs">
                                            {{ song.title }}
                                        </span>

                                        <div class="flex items-center gap-2">
                                            <User class="w-3.5 h-3.5 text-gray-500" />
                                            <span
                                                class="text-xs font-bold text-[var(--accent)] uppercase tracking-wide">
                                                {{ song.artist || 'Artista Desconocido' }}
                                            </span>
                                        </div>

                                        <div v-if="song.youtube_title"
                                            class="mt-0.5 flex items-start gap-1.5 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <span
                                                class="text-[10px] font-mono text-gray-400 leading-none bg-white/5 px-2 py-1 rounded border border-white/10 italic">
                                                <span class="text-red-500/80 mr-1 font-bold">YT:</span>
                                                {{ song.youtube_title }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-8 py-4 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/5 rounded-lg border border-white/5 text-xs font-black text-white italic shadow-sm">
                                            <BarChart3 class="w-3.5 h-3.5 text-[var(--accent)]" />
                                            {{ song.duration_seconds ? `${Math.floor(song.duration_seconds /
                                                60)}:${String(song.duration_seconds % 60).padStart(2, '0')}` : 'N/A' }}
                                        </span>

                                        <span v-if="song.channel_title"
                                            class="text-[10px] text-gray-500 uppercase font-black tracking-wider opacity-80 max-w-[180px] truncate">
                                            {{ song.channel_title }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-8 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(song)"
                                            class="p-3 text-white/30 hover:text-[var(--accent)] hover:bg-[var(--accent)]/10 rounded-xl transition-all active:scale-90 border border-transparent hover:border-[var(--accent)]/20">
                                            <Pencil class="w-5 h-5" />
                                        </button>

                                        <button @click="deleteFromLibrary(song.id)"
                                            class="p-3 text-white/20 hover:text-red-500 hover:bg-red-500/10 rounded-xl transition-all active:scale-90 border border-transparent hover:border-red-500/20">
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <EmptyState v-else title="Sin Hits" />
                </div>

                <div class="p-6 border-t border-white/5 bg-black/10">
                    <Pagination :links="songs.links" />
                </div>
            </div>
        </div>

        <!-- MODAL EDITAR -->
        <Modal :show="showEditModal" max-width="2xl" @close="closeEditModal">
            <div class="p-6 md:p-8">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-black text-white uppercase italic tracking-widest">
                            Editar <span class="text-[var(--accent)]">Hit</span>
                        </h2>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.25em] mt-2">
                            Corrige metadata y ordena tu catálogo
                        </p>
                    </div>

                    <button @click="closeEditModal"
                        class="p-2 rounded-xl text-white/40 hover:text-white hover:bg-white/5 transition">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="updateSong" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-[150px_1fr] gap-6">
                        <div>
                            <div
                                class="w-full aspect-video rounded-2xl overflow-hidden border border-white/10 bg-black/30">
                                <img :src="editForm.thumbnail_url || (editingSong ? `https://img.youtube.com/vi/${editingSong.youtube_id}/mqdefault.jpg` : '')"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase tracking-[0.25em] text-gray-400 mb-2">
                                    Título
                                </label>
                                <input v-model="editForm.title" type="text"
                                    class="w-full bg-black/30 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[var(--accent)]" />
                                <InputError :message="editForm.errors.title"
                                    class="mt-2 text-[10px] uppercase text-red-500" />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase tracking-[0.25em] text-gray-400 mb-2">
                                    Artista
                                </label>
                                <input v-model="editForm.artist" type="text"
                                    class="w-full bg-black/30 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[var(--accent)]" />
                                <InputError :message="editForm.errors.artist"
                                    class="mt-2 text-[10px] uppercase text-red-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" @click="closeEditModal"
                            class="px-5 py-3 rounded-xl border border-white/10 text-white/70 hover:text-white hover:bg-white/5 transition text-xs font-black uppercase tracking-[0.2em]">
                            Cancelar
                        </button>

                        <PrimaryButton type="submit" :disabled="editForm.processing"
                            :style="{ borderRadius: '0.75rem' }">
                            <Loader2 v-if="editForm.processing" class="w-4 h-4 animate-spin mr-2" />
                            <Pencil v-else class="w-4 h-4 mr-2" />
                            Guardar cambios
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
* {
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
}
</style>