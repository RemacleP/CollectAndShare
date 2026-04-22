<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    // Si on est Pascal (is_admin = 1), on force le retour pour tester
    if ($user->is_admin == 1) {
        return [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'is_admin' => true
        ];
    }

    // Pour les autres, vérification normale
    $conversation = \App\Models\Conversation::find($conversationId);
    if (!$conversation) return false;

    return [
        'id' => $user->id,
        'firstname' => $user->firstname,
        'lastname' => $user->lastname,
        'is_admin' => false
    ];
});
