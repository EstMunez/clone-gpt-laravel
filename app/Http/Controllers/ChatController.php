<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
}
