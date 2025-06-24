<script setup>
import { ref, onMounted } from 'vue'

const show = ref(false)
onMounted(() => {
    show.value = true
})
</script>

<template>
    <div class="p-6 max-w-md mx-auto mt-20 bg-gray-100 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Test animation fade-in-up</h2>
        <div
            v-if="show"
            class="animate-fade-in-up bg-indigo-500 text-white p-4 rounded"
        >
            Je devrais apparaître en fondu + translation vers le haut !
        </div>
    </div>
</template>

<style>
@keyframes fade-in-up {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.35s ease forwards;
}
</style>
