<script setup>
import QrcodeVue from 'qrcode.vue';
defineProps({ table: Object, settings: Object, qrUrl: String });
</script>

<template>
    <div class="flex flex-col items-center justify-between py-12 px-10 relative"
        style="width: 148mm; height: 210mm; background-color: #12141c;">

        <div class="absolute inset-6 border-2 opacity-30" :style="{ borderColor: settings?.accent_color }"></div>

        <div class="z-10 text-center flex flex-col items-center">
            <h1 class="text-4xl font-black text-white italic uppercase tracking-tighter">
                {{ settings?.local_name || 'CANTALOPE' }}
            </h1>
            <p
                class="text-gray-400 text-[10px] font-bold uppercase tracking-[0.3em] mt-2 max-w-[100mm] text-center leading-relaxed">
                {{ settings?.description }}
            </p>
        </div>

        <div class="z-10 relative">
            <div class="bg-white p-6 rounded-[2.5rem] shadow-2xl">
                <qrcode-vue :value="qrUrl" :size="260" level="H" render-as="svg"
                    :foreground="settings?.accent_color || '#000000'" />
            </div>

            <div v-if="settings?.logo_path"
                class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-16 h-16 bg-white rounded-full p-1 shadow-xl">
                    <img :src="'/storage/' + settings.logo_path" class="w-full h-full rounded-full object-cover">
                </div>
            </div>
        </div>

        <div class="z-10 text-center">
            <span class="text-[10px] font-black tracking-[0.5em] uppercase opacity-60"
                :style="{ color: settings?.accent_color }">
                Estás en la mesa
            </span>
            <h2 class="text-9xl font-black text-white italic leading-none mt-2">
                {{ table.identifier }}
            </h2>
        </div>

        <p class="z-10 text-[8px] text-gray-600 font-mono tracking-widest uppercase">
            {{ settings?.local_name }} // v3.0 CantaloPe
        </p>
    </div>
</template>