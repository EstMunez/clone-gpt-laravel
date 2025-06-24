<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    models: Array,
    message: String,
    error: String,
    selectedModel: String,
    history: Array,
    conversations: Array,
})

const validModels = computed(() => (props.models ?? []).filter(m => m?.id && m?.name))

const form = useForm({
    message: '',
    model: '',
    history: [...props.history],
})

watch(validModels, (models) => {
    if (!form.model && models.length > 0) {
        form.model = props.selectedModel || models[0].id
    }
}, { immediate: true })

watch(() => props.selectedModel, (newVal) => {
    if (newVal && newVal !== form.model) {
        form.model = newVal
    }
})

const scrollToBottom = () => {
    nextTick(() => {
        const container = document.getElementById('chat-container')
        if (container) {
            container.scrollTop = container.scrollHeight
        }
    })
}

watch(() => form.history, scrollToBottom, { deep: true })

const clearHistory = () => {
    if (confirm('Effacer l’historique ?')) {
        form.history = []
        Inertia.delete(route('history.clear'))
    }
}

function formatDate(date) {
    return new Date(date).toLocaleString()
}

function loadConversation(conversation) {
    Inertia.get(route('conversations.show', conversation.id), {}, {
        preserveState: true,
        onSuccess: page => {
            form.history = page.props.conversation.history || []
            form.model = page.props.conversation.model || props.selectedModel || (validModels.value.length ? validModels.value[0].id : '')
            scrollToBottom()
        }
    })
}

const submitForm = async () => {
    if (!form.model && validModels.value.length) {
        form.model = validModels.value[0].id
        await nextTick()
    }
    form.post(route('ask.send'), {
        onSuccess: (page) => {
            if (page.props.history) {
                form.history = page.props.history
            }
            form.reset('message')
            scrollToBottom()
        },
        onError: () => console.log('Erreur', form.errors),
    })
}

function startNewConversation() {
    Inertia.post(route('ask.newConversation'))
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center p-6 font-sans text-gray-900">
        <div class="w-full max-w-4xl flex flex-col bg-white rounded-xl shadow-md overflow-hidden">

            <!-- Header -->
            <header class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-3xl font-semibold tracking-tight">MiniChatgpt </h1>
                <button
                    @click="startNewConversation"
                    class="text-indigo-600 hover:text-indigo-800 font-semibold transition"
                    title="Nouvelle conversation"
                >
                    + Nouvelle conversation
                </button>
            </header>

            <!-- Chat container -->
            <section
                id="chat-container"
                class="flex-grow overflow-y-auto px-6 py-4 space-y-4 bg-gray-50 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
                style="max-height: 500px;"
            >
                <template v-for="(msg, index) in form.history" :key="index">
                    <div
                        :class="[
              'max-w-[70%] px-5 py-3 rounded-2xl shadow-sm text-sm whitespace-pre-wrap',
              msg.role === 'user'
                ? 'ml-auto bg-indigo-600 text-white rounded-tr-none flex items-start space-x-3'
                : 'mr-auto bg-gray-200 text-gray-900 rounded-tl-none flex items-start space-x-3'
            ]"
                    >
                        <div class="text-xl select-none mt-1" aria-hidden="true">
                            {{ msg.role === 'user' ? '🧑' : '🤖' }}
                        </div>
                        <div>
                            <strong class="block text-xs uppercase tracking-wide text-gray-400 mb-1 select-none">
                                {{ msg.role === 'user' ? 'Vous' : 'Assistant' }}
                            </strong>
                            <p>{{ msg.content }}</p>
                        </div>
                    </div>
                </template>

                <p
                    v-if="form.processing && !props.message"
                    class="italic text-gray-500 flex items-center space-x-2 select-none"
                >
                    <span>L’IA réfléchit</span>
                    <span class="dot-flash"></span>
                </p>
            </section>

            <!-- Formulaire -->
            <form @submit.prevent="submitForm" class="border-t border-gray-200 px-6 py-4 flex flex-col gap-4 bg-white">
        <textarea
            v-model="form.message"
            rows="3"
            placeholder="Écris ta question ici..."
            class="resize-none rounded-md border border-gray-300 p-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            :class="{ 'border-red-500': form.errors.message }"
            required
        ></textarea>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <select
                        v-model="form.model"
                        class="flex-grow rounded-md border border-gray-300 p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option v-for="model in validModels" :key="model.id" :value="model.id">
                            🤖 {{ model.name }}
                        </option>
                    </select>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-md transition"
                        >
                            Envoyer
                        </button>
                        <button
                            type="button"
                            @click="clearHistory"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-md transition"
                        >
                            Effacer l’historique
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.message" class="text-red-500 text-sm mt-1">{{ form.errors.message }}</p>
            </form>

            <!-- Conversations précédentes -->
            <section class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-lg font-semibold mb-3">Conversations précédentes</h2>
                <ul class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                    <li v-for="conv in conversations" :key="conv.id">
                        <button
                            @click="loadConversation(conv)"
                            class="text-indigo-600 hover:text-indigo-800 underline font-medium truncate w-full text-left"
                            title="Charger la conversation"
                        >
                            {{ formatDate(conv.updated_at) }} — {{ conv.title || 'Sans titre' }}
                        </button>
                    </li>
                </ul>
            </section>

            <!-- Erreur -->
            <p v-if="error" class="text-center text-red-600 font-semibold py-2">
                {{ error }}
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes dotFlash {
    0%, 100% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
}

.dot-flash::after {
    content: '...';
    animation: dotFlash 1s infinite;
    display: inline-block;
    margin-left: 4px;
    font-weight: bold;
    color: inherit;
}

.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
    background-color: #d1d5db; /* Tailwind gray-300 */
    border-radius: 9999px;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
    background-color: #f3f4f6; /* Tailwind gray-100 */
    border-radius: 9999px;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
</style>

