<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github.css'
import { route } from 'ziggy-js'

const props = defineProps({
    models: Array,
    message: String,
    error: String,
    selectedModel: String,
    history: Array,
})

const validModels = computed(() => props.models.filter(m => m?.id && m?.name))

const form = useForm({
    message: '',
    model: props.selectedModel ?? validModels.value[0]?.id ?? '',
    history: Array.isArray(props.history) ? [...props.history] : [],
})

watch(() => props.selectedModel, (newVal) => {
    if (newVal && newVal !== form.model) {
        form.model = newVal
    }
})

const md = new MarkdownIt({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return '<pre class="hljs"><code>' + hljs.highlight(str, { language: lang }).value + '</code></pre>'
            } catch (_) {}
        }
        return '<pre class="hljs"><code>' + md.utils.escapeHtml(str) + '</code></pre>'
    },
})

const historyContainer = ref(null)

watch(() => form.history.length, () => {
    if (historyContainer.value) {
        historyContainer.value.scrollTop = historyContainer.value.scrollHeight
    }
})
</script>

<template>
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Pose ta question à l'IA</h1>

        <form
            @submit.prevent="form.post(route('ask.send'), {
                onSuccess: () => {
                    form.history.push({ role: 'user', content: form.message })
                    form.history.push({ role: 'assistant', content: props.message })
                    form.reset('message')
                },
                onError: () => console.log('Erreur', form.errors)
            })"
            class="space-y-4"
        >
            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea
                    v-model="form.message"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                ></textarea>
                <div v-if="form.errors.message" class="text-red-500 text-sm mt-1">
                    {{ form.errors.message }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Modèle</label>
                <select
                    v-model="form.model"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option v-for="model in validModels" :key="model.id" :value="model.id">
                        {{ model.name }}
                    </option>
                </select>
            </div>
            <pre>{{ props.history }}</pre>

            <button
                type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                :disabled="form.processing"
            >
                Envoyer
            </button>
        </form>

        <!-- Affichage de l'historique avec animation et scroll auto -->
        <div
            v-if="form.history.length"
            class="mt-6 space-y-4 overflow-y-auto max-h-96"
            ref="historyContainer"
        >
            <transition-group name="fade" tag="div">
                <div
                    v-for="(entry, index) in form.history"
                    :key="entry.role + '-' + index"
                    :class="entry.role === 'user' ? 'bg-gray-100 text-left' : 'bg-indigo-100 text-right'"
                    class="p-4 rounded-lg shadow-md transition duration-300 ease-in-out"
                >
                    <strong class="block text-xs uppercase text-gray-500">{{ entry.role }}</strong>
                    <p class="whitespace-pre-line text-gray-800" v-html="md.render(entry.content)"></p>
                </div>
            </transition-group>
        </div>

        <div
            v-if="message"
            class="mt-6 prose max-w-none"
            v-html="md.render(message)"
        ></div>

        <div v-if="error" class="mt-6 text-red-600">
            {{ error }}
        </div>

        <div v-if="message" class="text-green-600 mt-4">
            Message reçu du serveur : {{ message }}
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
