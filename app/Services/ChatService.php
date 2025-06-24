<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatService
{
    private $baseUrl;
    private $apiKey;
    private $client;
    public const DEFAULT_MODEL = 'openai/gpt-4.1-mini';

    public function __construct()
    {
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->apiKey = config('services.openrouter.api_key');
        $this->client = $this->createOpenAIClient();
    }

    /**
     * @return array<array-key, array{
     *     id: string,
     *     name: string,
     *     context_length: int,
     *     max_completion_tokens: int,
     *     pricing: array{prompt: int, completion: int}
     * }>
     */
    public function getModels(): array
    {
        return cache()->remember('openai.models', now()->addHour(), function () {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/models');

            // Sécurité : récupérer 'data' avec fallback tableau vide
            $models = collect($response->json('data', []));

            // Filtrer les modèles qui ont un 'id' et un 'name'
            return collect($response->json('data', []))
                ->filter(fn ($model) => is_array($model) && isset($model['id'])) // ← s'assurer que c’est bien un tableau
                ->map(function ($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name'] ?? $model['id'], // fallback propre
                        'context_length' => $model['context_length'] ?? null,
                        'max_completion_tokens' => $model['top_provider']['max_completion_tokens'] ?? null,
                        'pricing' => $model['pricing'] ?? null,
                    ];
                })
                ->sortBy('name')
                ->values()
                ->all();

        });
    }



    /**
     * @param array{role: 'user'|'assistant'|'system'|'function', content: string} $messages
     * @param string|null $model
     * @param float $temperature
     *
     * @return string
     */
    public function sendMessage(array $messages, string $model = null, float $temperature = 0.7): string
    {
        try {
            logger()->info('Envoi du message', [
                'model' => $model,
                'temperature' => $temperature,
            ]);

            // Récupérer tous les modèles disponibles
            $models = collect($this->getModels());

            // Vérifier si le modèle fourni est valide
            $selectedModel = $models->firstWhere('id', $model);

            // Si aucun modèle trouvé, on utilise le modèle par défaut
            if (!$selectedModel) {
                $model = self::DEFAULT_MODEL;
                logger()->info('Modèle non trouvé, modèle par défaut utilisé:', ['model' => $model]);
            } else {
                $model = $selectedModel['id'];
                logger()->info('Modèle sélectionné:', ['model' => $model]);
            }

            // Ajouter le système prompt
            $messages = [$this->getChatSystemPrompt(), ...$messages];

            // Appel à l'API
            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

            logger()->info('Réponse reçue:', ['response' => $response]);

            $content = $response->choices[0]->message->content;

            return $content;
        } catch (\Exception $e) {
            logger()->error('Erreur dans sendMessage:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }


    private function createOpenAIClient(): \OpenAI\Client
    {
        return \OpenAI::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUri($this->baseUrl)
            ->make()
            ;
    }

    /**
     * @return array{role: 'system', content: string}
     */
    private function getChatSystemPrompt(): array
    {
        $user = auth()->user();
        $now = now()->locale('fr')->format('l d F Y H:i');

        $userName = ($user && isset($user->name)) ? $user->name : 'un utilisateur invité';

        return [
            'role' => 'system',
            'content' => <<<EOT
                Tu es un assistant de chat. La date et l'heure actuelle est le {$now}.
                Tu es actuellement utilisé par {$userName}.
                EOT,
        ];
    }
}
