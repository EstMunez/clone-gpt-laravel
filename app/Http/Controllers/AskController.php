<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Services\ChatService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;



class AskController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    public function send(Request $request)
    {
        $user = auth()->user();
        $messageText = $request->input('message');

        // Vérifier si une conversation est déjà stockée en session
        $conversationId = session('conversation_id');

        if ($conversationId) {
            // Récupérer la conversation en session
            $conversation = Conversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            // Si conversation introuvable (ex: supprimée), créer une nouvelle
            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'title' => 'Conversation continue',
                ]);
                session(['conversation_id' => $conversation->id]);
            }
        } else {
            // Pas de conversation en session => créer et stocker
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'title' => 'Conversation continue',
            ]);
            session(['conversation_id' => $conversation->id]);
        }

        // Ajouter le message utilisateur
        $message = $conversation->messages()->create([
            'role' => 'user',
            'content' => $messageText,
        ]);

        // Mettre à jour automatiquement le titre si c’est le tout premier message
        if ($conversation->messages()->count() === 1) {
            $shortTitle = Str::limit($messageText, 50, '...');
            $conversation->update(['title' => $shortTitle]);
        }

        // Récupérer l’historique
        $history = $conversation->messages()->orderBy('created_at')->get(['role', 'content'])->toArray();

        $model = $user->model ?? null;
        $aiResponse = $this->chatService->sendMessage($history, $model);

        // Stocker la réponse assistant
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        // Recharger l’historique après ajout assistant
        $history = $conversation->messages()->orderBy('created_at')->get(['role', 'content'])->toArray();

        return Inertia::render('Ask/Index', [
            'conversations' => $user->conversations()->latest()->get(),
            'message' => $message,
            'messageAI' => $aiResponse,
            'history' => $history,
            'selectedModel' => $model,
            'models' => $this->chatService->getModels(),
        ]);



    }

    public function newConversation(Request $request)
    {
        $request->session()->forget('conversation_id');
        return redirect()->route('ask.index'); // adapte le nom de la route vers ta page principale du chat
    }

    //crée une nouvelle conversation
    public function index(Request $request)
    {
        $user = auth()->user();

        // On récupère la dernière conversation de l'utilisateur
        $conversation = $user->conversations()->latest()->first();

        $history = [];

        if ($conversation) {
            $history = $conversation->messages()
                ->orderBy('created_at')
                ->get(['role', 'content'])
                ->toArray();
        }

        return Inertia::render('Ask/Index', [
            'conversations' => $user->conversations()->latest()->get(),
            'history' => $history,
            'selectedModel' => $user->model ?? null,
            'models' => app(ChatService::class)->getModels(),
            'activeConversationId' => $conversation?->id,
        ]);
    }

    /**
     * Supprime l'historique des messages de l'utilisateur connecté.
     */
    public function clear()
    {
        $user = Auth::user();

        session()->forget('history');

        // Redirige avec un message ou retourne une réponse JSON
        return response()->json(['message' => 'Historique effacé'], 200);
    }


}
