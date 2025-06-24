<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiModelsTable extends Migration
{
    public function up()
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // Nom du modèle (ex : "GPT-4")
            $table->string('description')->nullable();  // Description optionnelle
            $table->string('api_key')->nullable();      // Si tu stockes une clé API liée au modèle (optionnel)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_models');
    }
}

