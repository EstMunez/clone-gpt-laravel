<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AskTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_utilisateur_connecte_peut_acceder_a_la_page_ask()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ask.index'));

        $response->assertStatus(200);

        $response->assertJsonFragment(['component' => 'Ask/Index']);
    }


    #[Test]
    public function un_utilisateur_non_connecte_est_redirige()
    {
        $response = $this->get(route('ask.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function un_utilisateur_peut_creer_une_nouvelle_conversation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('ask.newConversation'));

        $response->assertRedirect(); // redirection vers la nouvelle conversation
    }

    #[Test]
    public function un_message_est_envoye_et_reponse_recue()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('ask.store'), [
            'question' => 'Bonjour IA',
            'model' => config('openrouter.models')[0]['id'] ?? 'default-model',
            'history' => [],
        ]);

        $response->assertStatus(200); // ou 302 si redirection prévue
        $response->assertSessionHasNoErrors();
    }


}
