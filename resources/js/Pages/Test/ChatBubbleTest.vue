<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="w-full max-w-md space-y-4">

            <h2 class="text-xl font-bold text-center text-gray-800">💬 Test des bulles de chat</h2>

            <transition-group name="fade" tag="div" class="space-y-2">
                <div
                    v-for="(msg, index) in messages"
                    :key="index"
                    :class="[
            'p-3 rounded-2xl shadow max-w-[75%] transition-all duration-300',
            msg.role === 'user'
              ? 'ml-auto bg-indigo-500 text-white rounded-br-none'
              : 'mr-auto bg-gray-200 text-gray-900 rounded-bl-none'
          ]"
                >
                    <p class="text-sm whitespace-pre-line">{{ msg.content }}</p>
                </div>
            </transition-group>

            <div class="flex gap-2">
                <input
                    v-model="newMessage"
                    @keydown.enter="sendMessage"
                    type="text"
                    placeholder="Tape un message..."
                    class="flex-1 p-2 rounded border border-gray-300 focus:outline-none focus:ring focus:border-indigo-400"
                />
                <button
                    @click="sendMessage"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded"
                >
                    Envoyer
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const newMessage = ref('')
const messages = ref([
    { role: 'assistant', content: 'Bonjour, comment puis-je t’aider ?' },
    { role: 'user', content: 'Montre-moi un test Tailwind.' }
])

function sendMessage() {
    if (newMessage.value.trim() !== '') {
        messages.value.push({ role: 'user', content: newMessage.value })
        newMessage.value = ''
        setTimeout(() => {
            messages.value.push({ role: 'assistant', content: 'Réponse automatique 😄' })
        }, 500)
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>

