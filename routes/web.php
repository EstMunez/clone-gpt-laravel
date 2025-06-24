<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AskController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\IndexController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d’accueil redirigée vers le chat si l’utilisateur est connecté
Route::get('/', [IndexController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('index');

// Routes accessibles uniquement après authentification
Route::middleware(['auth', 'verified'])->group(function () {

    // Nouvelle conversation
    Route::post('/ask/new-conversation', [AskController::class, 'newConversation'])->name('ask.newConversation');

    // Page principale du chat
    Route::get('/ask', [AskController::class, 'index'])->name('ask.index');

    // Envoi d’un message à l’IA
    Route::post('/ask', [AskController::class, 'send'])->name('ask.send');

    // Affichage d’une conversation spécifique
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    // Suppression de l’historique complet
    Route::delete('/history/clear', [AskController::class, 'clear'])->name('history.clear')->middleware('auth');

    // Page "Chat"  /ask  (peux la supprimer si inutile)
    Route::get('/chat', [IndexController::class, 'index'])->name('chat');

    // Dashboard
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // Gestion du profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentification (Jetstream ou Breeze)
require __DIR__ . '/auth.php';
