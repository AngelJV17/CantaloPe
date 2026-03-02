<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import QrcodeVue from 'qrcode.vue';
import Modal from '@/Components/Modal.vue';
import TableForm from './Partials/TableForm.vue';
import {
    Plus, Trash2, MapPin, Users, QrCode,
    Settings2, X, Download, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    tables: Object,
    settings: Object, // Traído desde el controlador
});

const page = usePage();
const showModal = ref(false);
const isEditing = ref(false);
const selectedTable = ref(null);
const editingId = ref(null);
const isDownloading = ref(false);

// Settings dinámicos (Igual que en tu Dashboard)
const brandSettings = computed(() => page.props.auth.settings);

const form = useForm({
    name: '',
    identifier: '',
    capacity: 4,
    zone: 'General',
});

const getTableUrl = (uuid) => `${window.location.origin}/order/table/${uuid}`;

// --- LÓGICA DE EXPORTACIÓN ---
const downloadQR = () => {
    isDownloading.value = true;
    const svg = document.querySelector('#qr-container svg');
    if (!svg) return;

    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    const img = new Image();

    img.onload = () => {
        canvas.width = 1000;
        canvas.height = 1200;
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Dibujar QR
        ctx.drawImage(img, 100, 150, 800, 800);

        // Branding en la descarga
        ctx.fillStyle = brandSettings.value?.accent_color || "#6366f1";
        ctx.font = "bold 60px Inter, sans-serif";
        ctx.textAlign = "center";
        ctx.fillText(brandSettings.value?.local_name?.toUpperCase() || "CANTALOPE", 500, 100);

        ctx.fillStyle = "#111827";
        ctx.font = "bold 40px Inter, sans-serif";
        ctx.fillText(`MESA: ${selectedTable.value.identifier}`, 500, 1050);

        const link = document.createElement("a");
        link.download = `QR_${selectedTable.value.identifier}.png`;
        link.href = canvas.toDataURL("image/png");
        link.click();
        isDownloading.value = false;
    };

    img.src = "data:image/svg+xml;base64," + btoa(unescape(encodeURIComponent(svgData)));
};

// --- MÉTODOS ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (table) => {
    isEditing.value = true;
    editingId.value = table.id;
    form.name = table.name;
    form.identifier = table.identifier;
    form.capacity = table.capacity;
    form.zone = table.zone;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.clearErrors();
};

const submit = () => {
    const method = isEditing.value ? 'put' : 'post';
    const url = isEditing.value ? route('tables.update', editingId.value) : route('tables.store');
    form[method](url, { onSuccess: () => closeModal(), preserveScroll: true });
};

const deleteTable = (id) => {
    Swal.fire({
        title: '¿ELIMINAR MESA?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: brandSettings.value?.accent_color || '#6366f1',
        cancelButtonColor: '#374151',
        background: '#12141c',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('tables.destroy', id), { preserveScroll: true });
        }
    });
};
</script>

<template>

    <Head title="Gestión de Mesas" />

    <AuthenticatedLayout>
        <template #header><span>Gestión de Mesas</span></template>

        <div class="max-w-7xl mx-auto pb-20 px-4 mt-8">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-white uppercase italic tracking-tighter">
                        Mapa de <span :style="{ color: brandSettings?.accent_color || '#6366f1' }">Sala</span>
                    </h2>
                    <div class="flex items-center gap-2">
                        <div class="h-[1px] w-8 bg-white/10"></div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em]">Puntos de interacción
                            QR</p>
                    </div>
                </div>

                <button @click="openCreateModal"
                    class="group flex items-center gap-3 px-8 py-4 text-white font-black text-xs uppercase transition-all shadow-2xl hover:brightness-110 active:scale-95"
                    :style="{
                        backgroundColor: brandSettings?.accent_color || '#6366f1',
                        borderRadius: brandSettings?.border_radius || '1rem'
                    }">
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    Registrar Mesa
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="table in tables.data" :key="table.id"
                    class="bg-[#12141c]/50 border border-white/5 backdrop-blur-md p-6 flex flex-col justify-between min-h-[250px] group hover:border-white/10"
                    :style="{ borderRadius: brandSettings?.border_radius || '1.25rem' }">

                    <div>
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <span class="text-[9px] font-black uppercase tracking-widest opacity-50 italic"
                                    :style="{ color: brandSettings?.accent_color || '#6366f1' }">
                                    REF: {{ table.identifier }}
                                </span>
                                <h3 class="text-xl font-bold text-white uppercase truncate">{{ table.name }}</h3>
                            </div>
                            <button @click="openEditModal(table)"
                                class="p-2 hover:bg-white/5 rounded-full text-gray-500 hover:text-white transition-colors">
                                <Settings2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-4">
                            <div class="flex items-center gap-2 text-gray-400">
                                <MapPin class="w-3.5 h-3.5" />
                                <span class="text-[10px] font-bold uppercase">{{ table.zone }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-400">
                                <Users class="w-3.5 h-3.5" />
                                <span class="text-[10px] font-bold uppercase">{{ table.capacity }} Pax</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-8">
                        <button @click="selectedTable = table"
                            class="flex items-center justify-center gap-2 p-3 bg-white/5 hover:bg-white/10 text-white font-bold text-[9px] uppercase tracking-widest transition-all"
                            :style="{ borderRadius: 'calc(' + (brandSettings?.border_radius || '1rem') + ' / 2)' }">
                            <QrCode class="w-3.5 h-3.5" /> QR Code
                        </button>
                        <button @click="deleteTable(table.id)"
                            class="flex items-center justify-center gap-2 p-3 bg-red-500/5 hover:bg-red-500/10 text-red-500/50 hover:text-red-500 font-bold text-[9px] uppercase tracking-widest transition-all"
                            :style="{ borderRadius: 'calc(' + (brandSettings?.border_radius || '1rem') + ' / 2)' }">
                            <Trash2 class="w-3.5 h-3.5" /> Borrar
                        </button>
                    </div>
                </div>
            </div>

            <transition name="scale">
                <div v-if="selectedTable"
                    class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/95 backdrop-blur-2xl"
                    @click.self="selectedTable = null">

                    <div class="w-full max-w-sm overflow-hidden border border-white/10 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] relative"
                        :style="{
                            backgroundColor: brandSettings?.sidebar_color || '#12141c',
                            borderRadius: brandSettings?.border_radius || '2.5rem',
                            fontFamily: brandSettings?.font_family || 'sans-serif'
                        }">

                        <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full blur-[80px] opacity-20"
                            :style="{ backgroundColor: brandSettings?.accent_color }"></div>

                        <div class="p-8 pb-4 flex justify-between items-center relative z-10">
                            <div class="flex items-center gap-3">
                                <div v-if="brandSettings?.logo_path"
                                    class="w-10 h-10 rounded-full overflow-hidden border border-white/10 bg-black/20">
                                    <img :src="'/storage/' + brandSettings.logo_path"
                                        class="w-full h-full object-cover">
                                </div>
                                <h4
                                    class="text-white font-black uppercase italic tracking-tighter text-lg leading-none">
                                    {{ brandSettings?.local_name || 'CANTALOPE' }}
                                </h4>
                            </div>
                            <button @click="selectedTable = null"
                                class="p-2 text-gray-500 hover:text-white transition-colors">
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="px-8 pb-10 text-center relative z-10">

                            <div class="relative inline-block group mb-8">
                                <div class="absolute -inset-4 rounded-[2rem] opacity-20 group-hover:opacity-40 transition-opacity"
                                    :style="{ background: `linear-gradient(135deg, ${brandSettings?.accent_color}, ${brandSettings?.secondary_color})` }">
                                </div>

                                <div id="qr-container" class="bg-white p-6 rounded-[2rem] shadow-2xl relative z-10">
                                    <qrcode-vue :value="getTableUrl(selectedTable.uuid)" :size="220" level="H"
                                        render-as="svg" :foreground="brandSettings?.accent_color || '#000000'" />

                                    <div v-if="brandSettings?.logo_path"
                                        class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div
                                            class="w-14 h-14 bg-white rounded-full p-1 shadow-lg border-2 border-white">
                                            <img :src="'/storage/' + brandSettings.logo_path"
                                                class="w-full h-full rounded-full object-cover">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 mb-10">
                                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-gray-500">Mesa
                                    Seleccionada</p>
                                <h3 class="text-5xl font-black italic tracking-tighter text-white">
                                    {{ selectedTable.identifier }}
                                </h3>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{
                                    selectedTable.name }}</p>
                            </div>

                            <button @click="downloadQR" :disabled="isDownloading"
                                class="w-full py-5 flex items-center justify-center gap-3 text-white font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50"
                                :style="{
                                    background: `linear-gradient(135deg, ${brandSettings?.accent_color || '#6366f1'}, ${brandSettings?.secondary_color || '#4f46e5'})`,
                                    borderRadius: brandSettings?.border_radius || '1rem'
                                }">
                                <component :is="isDownloading ? Loader2 : Download" class="w-5 h-5"
                                    :class="{ 'animate-spin': isDownloading }" />
                                {{ isDownloading ? 'Generando Archivo...' : 'Exportar para Impresión' }}
                            </button>
                        </div>

                        <div class="h-2 w-full opacity-50"
                            :style="{ background: `linear-gradient(90deg, ${brandSettings?.accent_color}, transparent, ${brandSettings?.secondary_color})` }">
                        </div>
                    </div>
                </div>
            </transition>

            <Modal :show="showModal" @close="closeModal" maxWidth="md">
                <div class="p-8 bg-[#12141c]">
                    <h2 class="text-2xl font-black text-white uppercase italic mb-6">
                        {{ isEditing ? 'Editar' : 'Nueva' }} <span
                            :style="{ color: brandSettings?.accent_color }">Mesa</span>
                    </h2>
                    <TableForm :form="form" :isEditing="isEditing" @submit="submit" />
                </div>
            </Modal>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
* {
    transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.scale-enter-active,
.scale-leave-active {
    transition: all 0.4s ease;
}

.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>