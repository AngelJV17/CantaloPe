<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <Transition enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-show="message" class="mt-2 flex items-center space-x-1.5">
            <div class="w-1 h-1 rounded-full bg-red-500 animate-pulse"></div>

            <p
                class="text-[11px] font-bold uppercase tracking-wider text-red-400 drop-shadow-[0_0_5px_rgba(239,68,68,0.3)]">
                {{ message }}
            </p>
        </div>
    </Transition>
</template>

<style scoped>
/* Un efecto de sutil parpadeo para que el usuario no ignore el error */
p {
    text-rendering: optimizeLegibility;
}
</style>