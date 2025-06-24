<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
class IndexController extends Controller
{
    //

    public function index()
    {
        $user = auth()->user();

        // Simule une liste de modèles disponibles (à remplacer si tu as un Model en base)
        $models = [
            [ 'id' => 'gpt-3.5-turbo', 'name' => 'GPT 3.5 Turbo' ],
            [ 'id' => 'gpt-4.1-mini', 'name' => 'GPT-4.1 Mini' ],
        ];

        return Inertia::render('Ask/Index', [
            'models' => $models,
            'conversations' => $user->conversations()->orderBy('updated_at', 'desc')->get(),
            'conversation' => null,
            'history' => [],
            'selectedModel' => 'gpt-4.1-mini',
        ]);
    }

}
