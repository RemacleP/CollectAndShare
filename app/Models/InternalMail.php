<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InternalMail extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés massivement.
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'read_at',
        'archived_at',
        'reference_type',
        'reference_id',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     */
    protected $casts = [
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * L'expéditeur du message (peut être null si message système).
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Le destinataire du message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Relation polymorphique (permet de lier le mail à une demande, une commande, etc.)
     * Usage : $mail->reference
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope pour récupérer uniquement les messages non lus.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Vérifie si le message a été lu.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
