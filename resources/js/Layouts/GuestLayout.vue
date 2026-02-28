<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';// Iconografía
import { MicVocal } from 'lucide-vue-next';

/**
 * ESTÉTICA DE PRE-AUTENTICACIÓN (CANTALOPE DEFAULT)
 * Aquí forzamos los valores de tu marca para que el Login/Registro 
 * siempre se vea con la identidad de Cantalope.
 */
const cantalopeStyle = {
    '--bg-main': '#090a0f', // Tu negro profundo de sistema
    '--accent': '#6366f1',  // Tu índigo de sistema
    '--radius': '1.5rem',   // El radio "Full Style" que te gusta del Dashboard
    '--font-family': "'Inter', sans-serif",
};
</script>

<template>
    <div :style="cantalopeStyle"
        class="flex min-h-screen flex-col items-center justify-center bg-[var(--bg-main)] p-6 font-[family-name:var(--font-family)] relative overflow-hidden">

        <div class="absolute -top-24 -left-24 w-96 h-96 bg-[var(--accent)]/10 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[var(--accent)]/5 rounded-full blur-[120px]"></div>

        <div class="z-10 w-full sm:max-w-md">

            <div class="flex flex-col items-center mb-10">
                <Link href="/" class="relative group">
                    <div
                        class="absolute inset-0 bg-indigo-500/25 blur-[50px] opacity-40 group-hover:opacity-70 transition-opacity duration-700">
                    </div>

                    <div
                        class="relative w-36 h-36 flex items-center justify-center rounded-full bg-white/[0.03] border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.3)] backdrop-blur-2xl transition-all duration-500 group-hover:scale-110 group-hover:border-indigo-500/30">

                        <div class="relative">
                            <MicVocal class="w-16 h-16 text-indigo-500 absolute inset-0 blur-lg opacity-40" />
                            <MicVocal
                                class="relative w-16 h-16 text-white drop-shadow-[0_0_15px_rgba(99,102,241,0.8)] transition-all duration-500 group-hover:drop-shadow-[0_0_25px_rgba(99,102,241,1)]"
                                :stroke-width="1.5" />
                        </div>

                        <div class="absolute inset-3 border border-indigo-500/5 rounded-full"></div>
                    </div>
                </Link>
                <h1 class="mt-6 text-2xl font-black text-white uppercase tracking-[0.3em]">
                    Cantalo<span class="text-indigo-500">pe</span>
                </h1>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em] mt-2">v3.0 Station Control</p>
            </div>

            <div
                class="w-full overflow-hidden bg-white/[0.03] backdrop-blur-2xl border border-white/10 px-8 py-10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] rounded-[var(--radius)] relative">

                <div
                    class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-[var(--accent)]/50 to-transparent">
                </div>

                <div class="text-white">
                    <slot />
                </div>
            </div>

            <div class="mt-12 flex items-center justify-center gap-4">
                <div class="h-[1px] w-8 bg-white/5"></div>
                <p class="text-gray-600 text-[9px] font-bold uppercase tracking-[0.4em]">
                    &copy; {{ new Date().getFullYear() }} Cantalope System
                </p>
                <div class="h-[1px] w-8 bg-white/5"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.z-10 {
    animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>