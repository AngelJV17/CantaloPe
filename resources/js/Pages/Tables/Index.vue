<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import QrcodeVue from 'qrcode.vue';
import Modal from '@/Components/Modal.vue';
import TableForm from './Partials/TableForm.vue';
import TableQrPrint from './Partials/TableQrPrint.vue';
import { useTableExport } from '@/Composables/useTableExport';
import {
    Plus, Trash2, MapPin, Users, QrCode,
    Settings2, X, Download, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    tables: Object,
    settings: Object,
});

const page = usePage();
const brandSettings = computed(() => page.props.auth.settings);

// Estados
const showModal = ref(false);
const isEditing = ref(false);
const selectedTable = ref(null);
const editingTableId = ref(null);

const { downloadPDF, isDownloading } = useTableExport();

const form = useForm({
    name: '',
    identifier: '',
    capacity: 4,
    zone: 'General',
});

const getTableUrl = (uuid) => `${window.location.origin}/order/table/${uuid}`;

// CORRECCIÓN: Aseguramos que el ID coincida con el elemento del DOM
const handleDownload = async () => {
    if (!selectedTable.value) return;

    const elementId = `print-table-${selectedTable.value.id}`;
    const fileName = `QR_MESA_${selectedTable.value.identifier}`;

    try {
        await downloadPDF(elementId, fileName);
    } catch (error) {
        console.error("Error al descargar PDF:", error);
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    editingTableId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (table) => {
    isEditing.value = true;
    editingTableId.value = table.id;
    form.clearErrors();
    form.name = table.name;
    form.identifier = table.identifier;
    form.capacity = table.capacity;
    form.zone = table.zone;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

// --- LÓGICA DE TOAST CENTRALIZADA ---
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
        background: brandSettings.value?.sidebar_color || '#12141c',
        color: '#ffffff',
        customClass: {
            popup: 'border border-white/10 backdrop-blur-xl shadow-2xl',
            title: 'font-black italic uppercase tracking-tighter text-sm'
        },
        didOpen: (toast) => {
            toast.style.borderRadius = brandSettings.value?.border_radius || '1rem';
        }
    });

    if (successMsg) {
        Toast.fire({
            icon: 'success',
            title: String(successMsg).toUpperCase(),
            iconColor: brandSettings.value?.accent_color || '#6366f1'
        });
    }

    if (errorMsg) {
        Toast.fire({
            icon: 'error',
            title: String(errorMsg).toUpperCase()
        });
    }
}, { deep: true, immediate: true });

const deleteTable = (id) => {
    Swal.fire({
        title: '¿ELIMINAR MESA?',
        text: "ESTA ACCIÓN NO SE PUEDE DESHACER Y EL QR DEJARÁ DE FUNCIONAR.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, BORRAR MESA',
        cancelButtonText: 'CANCELAR',
        background: brandSettings.value?.sidebar_color || '#12141c',
        color: '#fff',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        reverseButtons: true,
        customClass: {
            popup: 'border border-white/10 backdrop-blur-2xl',
            title: 'font-black italic uppercase tracking-tighter text-2xl mt-4',
            htmlContainer: 'text-[10px] font-bold uppercase tracking-widest opacity-70 px-6',
            confirmButton: 'font-black uppercase text-[10px] px-8 py-4 tracking-widest mx-2',
            cancelButton: 'font-black uppercase text-[10px] px-8 py-4 tracking-widest mx-2',
        },
        didOpen: (popup) => {
            popup.style.borderRadius = brandSettings.value?.border_radius || '2rem';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('tables.destroy', id), { preserveScroll: true });
        }
    });
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true
    };

    if (isEditing.value) {
        form.put(route('tables.update', editingTableId.value), options);
    } else {
        form.post(route('tables.store'), options);
    }
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
                    class="bg-[#12141c]/50 border border-white/5 backdrop-blur-md p-6 flex flex-col justify-between min-h-[250px] group hover:border-white/10 transition-all"
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
                            :style="{ borderRadius: `calc(${brandSettings?.border_radius || '1rem'} / 2)` }">
                            <QrCode class="w-3.5 h-3.5" /> QR Code
                        </button>
                        <button @click="deleteTable(table.id)"
                            class="flex items-center justify-center gap-2 p-3 bg-red-500/5 hover:bg-red-500/10 text-red-500/50 hover:text-red-500 font-bold text-[9px] uppercase tracking-widest transition-all"
                            :style="{ borderRadius: `calc(${brandSettings?.border_radius || '1rem'} / 2)` }">
                            <Trash2 class="w-3.5 h-3.5" /> Borrar
                        </button>
                    </div>
                </div>
            </div>

            <transition name="scale">
                <div v-if="selectedTable"
                    class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/95 backdrop-blur-2xl"
                    @click.self="selectedTable = null">

                    <div class="w-full max-w-sm overflow-hidden border border-white/10 shadow-2xl relative" :style="{
                        backgroundColor: brandSettings?.sidebar_color || '#12141c',
                        borderRadius: brandSettings?.border_radius || '2.5rem',
                    }">

                        <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full blur-[80px] opacity-20"
                            :style="{ backgroundColor: brandSettings?.accent_color }"></div>

                        <div class="p-8 pb-0 flex justify-between items-center relative z-10">
                            <h4 class="text-white font-black uppercase italic tracking-tighter text-lg leading-none">
                                {{ brandSettings?.local_name || 'CANTALOPE' }}
                            </h4>
                            <button @click="selectedTable = null"
                                class="p-2 text-gray-500 hover:text-white transition-colors">
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="px-8 pb-10 flex flex-col items-center text-center relative z-10">

                            <div class="bg-white p-6 rounded-[2rem] shadow-2xl inline-block mt-8 mb-8">
                                <qrcode-vue :value="getTableUrl(selectedTable.uuid)" :size="200" level="H"
                                    render-as="svg" :foreground="brandSettings?.accent_color || '#000000'" />
                            </div>

                            <div class="space-y-1 mb-10">
                                <p class="text-[9px] font-bold uppercase tracking-[0.3em]"
                                    :style="{ color: brandSettings?.accent_color }">
                                    {{ brandSettings?.description || 'READY TO SERVE' }}
                                </p>
                                <h3 class="text-5xl font-black italic tracking-tighter text-white uppercase">
                                    {{ selectedTable.identifier }}
                                </h3>
                            </div>

                            <button @click="handleDownload" :disabled="isDownloading"
                                class="w-full py-5 flex items-center justify-center gap-3 text-white font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="{
                                    background: `linear-gradient(135deg, ${brandSettings?.accent_color || '#6366f1'}, ${brandSettings?.secondary_color || '#4f46e5'})`,
                                    borderRadius: brandSettings?.border_radius || '1rem'
                                }">
                                <component :is="isDownloading ? Loader2 : Download" class="w-5 h-5"
                                    :class="{ 'animate-spin': isDownloading }" />
                                {{ isDownloading ? 'Procesando...' : 'Descargar PDF A5' }}
                            </button>
                        </div>
                    </div>

                    <div class="absolute opacity-0 pointer-events-none -left-[9999px] -top-[9999px]">
                        <TableQrPrint :id="`print-table-${selectedTable.id}`" :table="selectedTable"
                            :settings="brandSettings" :qrUrl="getTableUrl(selectedTable.uuid)" />
                    </div>
                </div>
            </transition>

            <Modal :show="showModal" @close="closeModal" maxWidth="md">
                <div class="p-8 bg-[#12141c]">
                    <h2 class="text-2xl font-black text-white uppercase italic mb-6">
                        {{ isEditing ? 'Editar' : 'Nueva' }}
                        <span :style="{ color: brandSettings?.accent_color }">Mesa</span>
                    </h2>
                    <TableForm :form="form" :isEditing="isEditing" @submit="submit" />
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>