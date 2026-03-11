<script setup>
import { ref, computed, watchEffect } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

import {
    LayoutDashboard,
    Settings,
    Music,
    User,
    LogOut,
    ChevronLeft,
    Menu,
    X,
    LayoutGrid,
    ListMusic
} from 'lucide-vue-next'

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const isSidebarOpen = ref(true)
const showingNavigationDropdown = ref(false)

const page = usePage()

/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
*/

const settings = computed(() => page.props?.auth?.settings ?? {})
const user = computed(() => page.props?.auth?.user ?? {})

/*
|--------------------------------------------------------------------------
| GOOGLE FONT LOADER
|--------------------------------------------------------------------------
*/

watchEffect(() => {

    const font = settings.value?.font_family || 'Inter'
    const linkId = 'dynamic-google-font'
    let link = document.getElementById(linkId)
    if (!link) {
        link = document.createElement('link')
        link.id = linkId
        link.rel = 'stylesheet'
        document.head.appendChild(link)
    }

    link.href = `https://fonts.googleapis.com/css2?family=${font.replace(/\s+/g, '+')}:wght@300;400;700;900&display=swap`
})

/*
|--------------------------------------------------------------------------
| THEME VARIABLES
|--------------------------------------------------------------------------
*/

const dynamicStyle = computed(() => ({
    '--bg-main': settings.value?.primary_color || '#090a0f',
    '--bg-sidebar': settings.value?.sidebar_color || '#12141c',
    '--accent': settings.value?.accent_color || '#6366f1',
    '--text': settings.value?.text_color || '#f3f4f6',
    '--radius': settings.value?.border_radius || '1rem',
    '--font-family': `'${settings.value?.font_family || 'Inter'}', sans-serif`
}))

const hasCustomLogo = computed(() => !!settings.value?.logo_path)

/*
|--------------------------------------------------------------------------
| NAVIGATION ITEMS
|--------------------------------------------------------------------------
*/

const navItems = [
    {
        name: 'Dashboard',
        route: 'dashboard',
        icon: LayoutDashboard
    },

    {
        name: 'Cola DJ',
        route: 'queues.index',
        icon: ListMusic
    },

    {
        name: 'Canciones',
        route: 'songs.index',
        icon: Music
    },

    {
        name: 'Mesas',
        route: 'tables.index',
        icon: LayoutGrid
    },

    {
        name: 'Ajustes',
        route: 'settings.edit',
        icon: Settings
    }
]

</script>

<template>

    <div :style="dynamicStyle"
        class="flex h-screen bg-[var(--bg-main)] text-[var(--text)] overflow-hidden font-[family-name:var(--font-family)]">

        <!-- SIDEBAR -->
        <aside :class="[isSidebarOpen ? 'w-64' : 'w-20']"
            class="hidden sm:flex flex-col m-4 mr-0 bg-[var(--bg-sidebar)]/90 border border-white/5 rounded-[var(--radius)] transition-[width] duration-300 ease-in-out z-20 shadow-2xl backdrop-blur-xl">

            <!-- LOGO -->
            <div class="flex items-center h-24 px-4 overflow-hidden shrink-0">
                <div class="flex items-center w-full">
                    <div class="flex items-center justify-center shrink-0 transition-all duration-500" :style="{
                        width: isSidebarOpen ? '52px' : '42px',
                        height: isSidebarOpen ? '52px' : '42px',
                        backgroundColor: hasCustomLogo ? 'transparent' : 'var(--accent)',
                        borderRadius: '50%',
                        border: hasCustomLogo ? '2px solid var(--accent)' : 'none',
                        padding: hasCustomLogo ? '4px' : '0'
                    }">

                        <img v-if="hasCustomLogo" :src="'/storage/' + settings.logo_path"
                            class="w-full h-full object-contain scale-[1.2]" />
                        <ApplicationLogo v-else class="w-7 h-7 text-white" />
                    </div>

                    <Transition name="fade">
                        <span v-show="isSidebarOpen"
                            class="ml-3 font-black text-lg tracking-tighter bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent italic uppercase truncate">
                            {{ settings?.local_name || 'CANTALOPE' }}
                        </span>
                    </Transition>
                </div>
            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 px-3 space-y-2 mt-4">
                <NavLink v-for="(item, index) in navItems" :key="index" :href="route(item.route)"
                    :active="route().current(item.route)" :isSidebarOpen="isSidebarOpen" :icon="item.icon">
                    {{ item.name }}
                </NavLink>
            </nav>

            <!-- FOOTER -->
            <div class="p-3 mt-auto border-t border-white/5 space-y-1">
                <Link :href="route('profile.edit')"
                    class="flex items-center h-14 px-3 text-gray-400 hover:text-[var(--accent)] hover:bg-white/5 rounded-[calc(var(--radius)*0.7)] transition-all group overflow-hidden">
                    <div class="flex items-center justify-center shrink-0 w-8 h-8">
                        <User class="w-6 h-6 transition-transform group-hover:scale-110" />
                    </div>
                    <Transition name="fade">
                        <div v-show="isSidebarOpen" class="ml-3 flex flex-col min-w-0 leading-tight">
                            <span class="text-sm font-bold truncate text-gray-200">
                                {{ user?.name }}
                            </span>
                            <span class="text-[10px] opacity-50 italic uppercase">
                                Mi Perfil
                            </span>
                        </div>
                    </Transition>
                </Link>

                <Link :href="route('logout')" method="post" as="button"
                    class="flex items-center w-full h-12 px-3 text-gray-500 hover:text-red-400 hover:bg-red-500/5 rounded-[calc(var(--radius)*0.7)] transition-all group">
                    <div class="flex items-center justify-center shrink-0 w-8 h-8">
                        <LogOut class="w-5 h-5 transition-transform group-hover:-translate-x-1" />
                    </div>
                    <span v-show="isSidebarOpen" class="ml-3 text-sm font-bold">
                        Cerrar Sesión
                    </span>
                </Link>
            </div>
        </aside>

        <!-- CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 p-4 overflow-hidden relative">
            <!-- HEADER -->
            <header
                class="h-16 mb-4 bg-[var(--bg-sidebar)]/50 backdrop-blur-md border border-white/5 rounded-[var(--radius)] flex items-center justify-between px-6 shadow-xl z-30">
                <div class="flex items-center space-x-4">
                    <button @click="isSidebarOpen = !isSidebarOpen"
                        class="hidden sm:block p-2 hover:bg-white/5 rounded-lg transition-colors group">
                        <ChevronLeft :class="{ 'rotate-180': !isSidebarOpen }"
                            class="w-5 h-5 transition-transform duration-500 text-gray-400 group-hover:text-[var(--accent)]" />
                    </button>

                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="sm:hidden p-2 hover:bg-white/5 rounded-lg transition-colors text-gray-400">
                        <Menu v-if="!showingNavigationDropdown" class="w-6 h-6" />
                        <X v-else class="w-6 h-6" />
                    </button>

                    <h2 v-if="$slots.header" class="text-xs font-black uppercase tracking-[0.4em]"
                        :style="{ color: 'var(--accent)' }">
                        <slot name="header" />
                    </h2>
                </div>
            </header>

            <!-- MAIN -->
            <main
                class="flex-1 bg-[var(--bg-sidebar)]/30 border border-white/5 rounded-[var(--radius)] overflow-y-auto p-6 scroll-smooth shadow-inner custom-scrollbar">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: color-mix(in srgb, var(--accent), transparent 70%);
    border-radius: 10px;
}

aside,
main,
header,
a,
button {
    transition-property: background-color, border-color, color, transform, width;
    transition-duration: 300ms;
}
</style>