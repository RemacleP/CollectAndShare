<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    /**
     * Affiche l'interface de chat pour un club et un salon spécifique.
     */
    public function show(Club $club, Conversation $conversation = null)
    {
        $user = auth()->user();
        // 0. Le Super Admin a tous les droits
        $isSuperAdmin = (bool) $user->is_admin;

        // 1. Vérifier les rôles au niveau du Club (Table: club_user_role)
        $isResponsable = $club->users()
            ->where('users.id', $user->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'responsable');
            })->exists();

        $isClubMember = $isSuperAdmin ||$club->users()->where('users.id', $user->id)->exists();

        // 2. Filtrage des conversations (Menu de gauche)
        // Logique : Responsable voit tout / Membre voit public + ses privés / Externe voit UNIQUEMENT ses invitations
        if ($isResponsable) {
            $conversations = $club->conversations()->get();
        } else {
            $conversations = $club->conversations()
                ->where(function ($query) use ($user, $isClubMember) {
                    // Si membre du club, accès aux salons publics du club
                    if ($isClubMember) {
                        $query->where('is_private', false);
                    }
                    // Dans tous les cas (Membre ou Externe), accès si invité explicitement
                    $query->orWhereHas('users', fn($q) => $q->where('user_id', $user->id));
                })->get();
        }

        // 3. Gestion du salon actif
        $messages = [];
        $activeUserRole = null;

        if ($conversation) {
            // Vérification de sécurité pour l'accès au salon sélectionné
            $membership = $conversation->users()->where('user_id', $user->id)->first();

            // Accès refusé si : Pas responsable ET (Salon privé ET Pas invité)
            // OU si Externe tentant d'accéder à un salon public sans y être invité
            $canAccess = $isResponsable || $membership || ($isClubMember && !$conversation->is_private);

            if (!$canAccess) {
                abort(403, "Vous n'avez pas l'autorisation d'accéder à ce salon.");
            }

            // Chargement des messages
            $messages = $conversation->messages()
                ->with('user:id,firstname,lastname')
                ->oldest()
                ->get();

            // Définition du rôle pour le frontend (Priorité au rôle global du club si responsable)
            if ($isResponsable) {
                $activeUserRole = 'responsable';
            } elseif ($membership) {
                $activeUserRole = $membership->pivot->role;
            } else {
                $activeUserRole = 'member';
            }
        }

        $conversationMembers = [];
        if ($conversation) {
            // On récupère tous les utilisateurs qui ont accès à ce salon
            // 1. Les membres du club
            // 2. Les invités spécifiques
            $conversationMembers = $conversation->club->users()
                ->where('users.is_admin', 0) // On exclut les admins (Pascal / ID 1)
                ->select('users.id', 'firstname', 'lastname')
                ->get()
                ->merge(
                    $conversation->users()
                        ->where('users.is_admin', 0)
                        ->select('users.id', 'firstname', 'lastname')
                        ->get()
                )
                ->unique('id');;
        }

        return Inertia::render('chat/Index', [
            'club' => $club,
            'conversations' => $conversations,
            'activeConversation' => $conversation,
            'messages' => $messages,
            'userRole' => $activeUserRole,
            'isClubMember' => $isClubMember,
            'conversationMembers' => $conversationMembers,
        ]);
    }

    /**
     * Création d'un nouveau salon (Réservé aux membres du club).
     */
    public function store(Request $request, Club $club)
    {
        $isClubMember = $club->users()->where('users.id', auth()->id())->exists();

        if (!$isClubMember) {
            abort(403, "Action réservée aux membres du club.");
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'is_private' => 'boolean',
        ]);

        $conversation = $club->conversations()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'is_private' => $validated['is_private'] ?? false,
        ]);

        // Le créateur devient modérateur par défaut
        $conversation->users()->attach(auth()->id(), ['role' => 'moderator']);

        return redirect()->route('clubs.chat.show', [
            'club' => $club->slug,
            'conversation' => $conversation->slug
        ]);
    }

    /**
     * Enregistre un nouveau message.
     */
    public function storeMessage(Request $request, Conversation $conversation)
    {
        $user = auth()->user();

        // Vérification du droit d'écriture
        $isResponsable = $conversation->club->users()
            ->where('users.id', $user->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'responsable'))
            ->exists();

        $isInvited = $conversation->users()->where('user_id', $user->id)->exists();
        $isPublicMember = !$conversation->is_private && $conversation->club->users()->where('users.id', $user->id)->exists();

        if (!$isResponsable && !$isInvited && !$isPublicMember) {
            abort(403, "Vous ne pouvez pas envoyer de message dans ce salon.");
        }

        $validated = $request->validate(['content' => 'required|string|max:2000']);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        $message->load('user:id,firstname,lastname');

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            Log::error('Erreur de diffusion Reverb : ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Ajoute un utilisateur à une conversation (Invitation).
     */
    public function addUser(Request $request, Conversation $conversation)
    {
        // Optionnel : Vérifier ici si auth()->user() est modérateur du salon ou responsable

        $request->validate(['user_id' => 'required|exists:users,id']);

        if (!$conversation->users()->where('user_id', $request->user_id)->exists()) {
            $conversation->users()->attach($request->user_id, ['role' => 'member']);
        }

        return back()->with('success', 'Utilisateur ajouté au salon.');
    }
}
