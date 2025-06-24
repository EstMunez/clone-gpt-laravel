<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function show(Conversation $conversation)
    {
        //$this->authorize('view', $conversation); // Si  sécuriser mais faut policy

        return Inertia::render('Ask/Index', [
            'models' => config('openrouter.models'),
            'selectedModel' => $conversation->model,
            'conversations' => $conversation->user->conversations()->latest()->get(),
            'history' => $conversation->messages()->get(['role', 'content']),
        ]);
    }

    public function clear(Request $request)
    {
        $request->user()->conversations()->delete();

        return back()->with('message', 'Historique effacé.');
    }
}
