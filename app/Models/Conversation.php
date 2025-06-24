<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'title', 'model', 'history'];

    protected $casts = [
        'history' => 'array',  // conversion automatique JSON <-> array
    ];

    // Relation : une conversation appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation : une conversation a plusieurs messages
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
