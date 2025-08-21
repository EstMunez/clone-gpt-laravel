<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = $user->conversations()->latest()->get();

        return Inertia::render('Ask/Index', [
            'models' => config('openrouter.models'), // Peut rester vide temporairement
            'selectedModel' => $user->model ?? null,
            'conversations' => $conversations,
            'history' => [],
        ]);
    }
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model' => 'nullable|string',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        $user = $request->user();

        // On garde le modèle choisi dans la table users
        if ($request->model) {
            $user->update(['model' => $request->model]);
        }

        // Crée ou récupère la conversation
        $conversation = Conversation::firstOrCreate(
            ['id' => $request->conversation_id, 'user_id' => $user->id],
            ['model' => $request->model ?? null]
        );

        // Enregistre le message utilisateur
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $request->message,
        ]);


        // Simuler la réponse IA (on va changer ça plus tard)
        $response = 'Réponse simulée de l’IA';

        // Enregistre la réponse IA
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
        ]);

        return response()->json([
            'message' => $response,
            'conversation_id' => $conversation->id,
        ]);
    }


    public function generateTitle(Conversation $conversation)
    {
        if ($conversation->user_id !== auth()->id()) {
            abort(403);
        }

        $firstMessage = $conversation->messages()->where('role', 'user')->first();

        if (!$firstMessage) {
            return response()->json(['error' => 'Aucun message à résumer'], 400);
        }

        // Simuler le titre généré
        $title = 'Résumé : ' . Str::limit($firstMessage->content, 50);

        $conversation->update(['title' => $title]);

        return response()->json(['title' => $title]);
    }

    public function stream(Request $request)
    {
        // Vérification utilisateur connecté
        $user = $request->user();

        $conversationId = $request->input('conversation_id');
        $prompt = $request->input('prompt');

        // Ex : récupération du LLM via ton service
        $llmService = app(\App\Services\ChatService::class);

        return new StreamedResponse(function () use ($llmService, $prompt, $conversationId, $user) {
            // Important : header SSE
            echo "retry: 1000\n"; // délai avant reconnexion auto
            flush();

            // Ici tu appelles ton API LLM (OpenRouter ou autre)
            foreach ($llmService->stream($prompt) as $chunk) {
                // chaque morceau est envoyé au frontend
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }

            // Fin du stream
            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
        ]);
    }
}
