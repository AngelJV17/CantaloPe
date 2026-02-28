<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Head, router, usePage } from '@inertiajs/vue3'; // Importamos usePage
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Swal from 'sweetalert2';
import { Youtube, Plus, Trash2, Music, BarChart3, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    songs: Object,
    filters: Object,
    settings: Object,
});

const page = usePage(); // Acceso al estado global de Inertia

// --- LÓGICA DE TOAST (IGUAL A SETTINGS) ---
watch(() => page.props.flash, (flash) => {
    if (!flash?.success && !flash?.message && !flash?.error) return;

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

    // Adaptamos para que lea 'success' o 'message'
    if (flash.success || flash.message) {
        Toast.fire({
            icon: 'success',
            title: flash.success || flash.message,
            iconColor: props.settings?.accent_color || '#6366f1'
        });
    }
    if (flash.error) {
        Toast.fire({ icon: 'error', title: flash.error });
    }
}, { deep: true });

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

// Lógica de filtrado
let timer = null;
watch(search, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('songs.index'), { search: v, perPage: perPage.value }, { preserveState: true, replace: true, preserveScroll: true });
    }, 400);
});

watch(perPage, (v) => {
    router.get(route('songs.index'), { search: search.value, perPage: v }, { preserveState: true, replace: true });
});

const themeStyles = computed(() => ({
    '--bg-card': props.settings?.sidebar_color || '#12141c',
    '--accent': props.settings?.accent_color || '#6366f1',
    '--radius': props.settings?.border_radius || '1.25rem',
    fontFamily: props.settings?.font_family || 'Inter',
}));

const form = useForm({ youtube_url: '' });

const submit = () => {
    form.post(route('songs.store'), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
};

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
            popup: 'rounded-[var(--radius)] border border-white/10 backdrop-blur-xl shadow-2xl',
            title: 'font-black uppercase italic tracking-widest text-xl',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('songs.destroy', id), { preserveScroll: true });
        }
    });
};
</script>

<template>

    <Head title="Biblioteca" />
    <AuthenticatedLayout>
        <template #header><span>Biblioteca Maestra</span></template>

        <div class="max-w-7xl mx-auto pb-20 px-4 mt-8 space-y-6" :style="themeStyles">

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
                        <input v-model="form.youtube_url" type="text" placeholder="URL DE YOUTUBE..."
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

            <div
                class="bg-[var(--bg-card)]/30 border border-white/5 backdrop-blur-xl shadow-2xl rounded-[var(--radius)] overflow-hidden">
                <div class="p-6 border-b border-white/5 bg-black/20 flex flex-col gap-5">
                    <div class="flex items-center gap-3">
                        <Music class="w-4 h-4 text-[var(--accent)]" />
                        <h3 class="font-black text-white uppercase text-xs tracking-[0.3em]">Hits Registrados</h3>
                    </div>

                    <TableFilters v-model:search="search" v-model:perPage="perPage"
                        placeholder="BUSCAR HIT O ID DEL VIDEO..." />
                </div>

                <div class="overflow-x-auto">
                    <table v-if="songs.data.length > 0" class="w-full text-left">
                        <thead>
                            <tr
                                class="text-[10px] font-black text-gray-600 uppercase tracking-widest border-b border-white/5 bg-black/20">
                                <th class="px-8 py-5 w-16">#</th>
                                <th class="px-4 py-5 text-center">Vista</th>
                                <th class="px-8 py-5">Información</th>
                                <th class="px-8 py-5 text-center">Plays</th>
                                <th class="px-8 py-5 text-right">Acciones</th>
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
                                        <img :src="`https://img.youtube.com/vi/${song.youtube_id}/mqdefault.jpg`"
                                            class="w-full h-full object-cover" />
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-[11px] font-black text-white uppercase truncate max-w-xs leading-none">
                                            {{ song.title }}
                                        </span>
                                        <span
                                            class="text-[9px] font-mono text-gray-600 uppercase tracking-tighter italic">
                                            ID: {{ song.youtube_id }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/5 rounded-lg border border-white/5 text-[10px] font-black text-white italic">
                                        <BarChart3 class="w-3 h-3 text-[var(--accent)]" />
                                        {{ song.times_played }}
                                    </span>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <button @click="deleteFromLibrary(song.id)"
                                        class="p-2.5 text-white/10 hover:text-red-500 hover:bg-red-500/10 rounded-xl transition-all active:scale-95">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
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
    </AuthenticatedLayout>
</template>

<style scoped>
* {
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
}
</style>