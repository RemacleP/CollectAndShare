<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Club;
use App\Models\InternalMail;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Affiche la vue du formulaire de demande d'inscription.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Gère la soumission de la demande d'inscription.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation des données alignée sur ton nouveau formulaire
        $request->validate([
            'firstname'         => 'required|string|max:255',
            'lastname'          => 'required|string|max:255',
            'username'          => 'required|string|max:255|unique:users,username|unique:registration_requests,username',
            'email'             => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'unique:users,email',
                'unique:registration_requests,email'
            ],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'registration_type' => 'required|in:user,club_manager',
            'club_id'           => 'nullable|required_if:registration_type,user',
            'new_club_name'     => 'nullable|required_if:club_id,new|string|max:255',
            'message'           => 'nullable|string|max:1000',
            'mentions_legales'  => 'accepted',
        ]);

        // 2. Création de la demande d'inscription (Table mise à jour)
        $registrationRequest = RegistrationRequest::create([
            'firstname'     => $request->firstname,
            'lastname'      => $request->lastname,
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'type'          => $request->registration_type,
            'club_id'       => $request->club_id === 'new' ? null : $request->club_id,
            'new_club_name' => $request->new_club_name,
            'message'       => $request->message,
            'status'        => 'pending',
        ]);

        // 3. Récupération de la liste des destinataires
        $receiverIds = $this->getReceiverIds($request);

        // 4. Envoi du message interne
        foreach ($receiverIds as $id) {
            InternalMail::create([
                'sender_id'      => null,
                'receiver_id'    => $id,
                'subject'        => 'Nouvelle demande : ' . $request->username,
                'body'           => $this->prepareMailBody($request),
                'reference_type' => RegistrationRequest::class,
                'reference_id'   => $registrationRequest->id,
            ]);
        }

        return redirect()->route('login')->with('status', 'Votre demande a été envoyée avec succès.');
    }

    /**
     * Approuve une demande et crée l'utilisateur.
     */
    public function approve($id)
    {
        // 1. On cherche la requête sans faire planter Laravel (404) si elle n'existe plus
        $requestData = \App\Models\RegistrationRequest::find($id);

        // Si la donnée est absente (déjà traitée ou supprimée)
        if (!$requestData) {
            return redirect()->back()->with('error', "Cette demande a déjà été traitée ou n'existe plus.");
        }

        try {
            return DB::transaction(function () use ($requestData) {

                // 1. CRÉATION DE L'UTILISATEUR
                $user = \App\Models\User::create([
                    'firstname' => $requestData->firstname,
                    'lastname'  => $requestData->lastname,
                    'username'  => $requestData->username,
                    'email'     => $requestData->email,
                    'password'  => $requestData->password, // Assure-toi qu'il est déjà haché ou hache-le ici
                    'email_verified_at' => now(), // On valide l'email puisqu'on approuve manuellement
                ]);

                // 2. CAS : CRÉATION D'UN NOUVEAU CLUB OU UTILISATION D'UN EXISTANT
                $clubId = $requestData->club_id;

                if ($requestData->type === 'club_manager' && $requestData->new_club_name) {
                    $club = \App\Models\Club::create([
                        'name'        => $requestData->new_club_name,
                        'slug'        => str($requestData->new_club_name)->slug() . '-' . uniqid(),
                        'description' => $requestData->club_description ?? $requestData->message,
                        'email'       => $requestData->email,
                    ]);

                    $clubId = $club->id;

                    // 2b. CRÉATION DE L'ADRESSE DU CLUB
                    if ($requestData->city) {
                        DB::table('addresses')->insert([
                            'street'           => $requestData->street,
                            'number'           => $requestData->number,
                            'postal_code'      => $requestData->postal_code,
                            'city'             => $requestData->city,
                            'country'          => $requestData->country ?? 'Belgique',
                            'addressable_type' => 'App\Models\Club',
                            'addressable_id'   => $clubId,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }

                    // 2c. CRÉATION DU SALON DE CHAT (Conversation par défaut)
                    \App\Models\Conversation::create([
                        'club_id'    => $clubId,
                        'title'      => "Général",
                        'slug'       => "general-" . $clubId . "-" . uniqid(),
                        'is_private' => false,
                    ]);
                }

                // 3. ATTRIBUTION DU RÔLE (Responsable ou Membre)
                $roleName = ($requestData->type === 'club_manager') ? 'responsable' : 'membre';
                $roleId = DB::table('roles')->where('name', $roleName)->value('id');

                // On attache l'utilisateur au club avec son rôle
                DB::table('club_user_role')->insert([
                    'user_id'    => $user->id,
                    'club_id'    => $clubId,
                    'role_id'    => $roleId ?? 1, // Fallback sur ID 1 si le rôle n'est pas trouvé
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. NETTOYAGE
                $requestData->delete();

                return redirect()->route('admin.users.index')->with('success', "Demande approuvée : Utilisateur créé et accès configurés.");
            });

        } catch (\Exception $e) {
            // En cas d'erreur dans la transaction (ex: email déjà pris), on annule tout et on revient avec l'erreur
            return redirect()->back()->with('error', "Erreur lors de la validation : " . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $requestData = RegistrationRequest::findOrFail($id);
        $authUser = auth()->user();

        // --- LOGIQUE DE SÉCURITÉ ---
        // On vérifie si l'utilisateur est admin ou responsable du club concerné
        if ($authUser->is_admin) {
            $isAuthorized = true;
        } else {
            $isAuthorized = $authUser->clubs()
                ->where('club_id', $requestData->club_id)
                ->wherePivot('role_id', function($q) {
                    $q->select('id')->from('roles')->where('name', 'responsable');
                })
                ->exists();
        }

        if (!$isAuthorized) {
            abort(403, "Vous n'avez pas les droits pour refuser cette demande.");
        }

        // Suppression de la demande
        $requestData->delete();

        return redirect()->route('messages.index')->with('info', "La demande d'inscription a été refusée et supprimée.");
    }

    /**
     * Détermine qui doit recevoir la notification (Admin ou Responsable).
     */
    private function getReceiverIds(Request $request): array
    {
        $adminId = User::where('is_admin', true)->value('id');

        if ($request->registration_type === 'club_manager' || $request->club_id === 'new') {
            return $adminId ? [$adminId] : [];
        }

        $responsableIds = DB::table('club_user_role')
            ->join('roles', 'club_user_role.role_id', '=', 'roles.id')
            ->where('club_user_role.club_id', $request->club_id)
            ->where('roles.name', 'responsable')
            ->pluck('user_id')
            ->toArray();

        return (empty($responsableIds) && $adminId) ? [$adminId] : $responsableIds;
    }

    /**
     * Génère le texte explicatif pour le corps du mail.
     */
    private function prepareMailBody(Request $request): string
    {
        $roleLabel = $request->registration_type === 'club_manager' ? 'Responsable de club' : 'Membre simple';
        $fullName = $request->firstname . ' ' . $request->lastname;

        if ($request->club_id === 'new') {
            $clubSection = "Action : Création du club \"{$request->new_club_name}\"";
        } else {
            $club = Club::find($request->club_id);
            $clubSection = "Club concerné : " . ($club ? $club->name : "Inconnu");
        }

        return "Bonjour,\n\n" .
            "Une nouvelle demande d'inscription est en attente :\n\n" .
            "- Utilisateur : {$fullName} (@{$request->username})\n" .
            "- Email : {$request->email}\n" .
            "- Type : {$roleLabel}\n" .
            "- {$clubSection}\n\n" .
            "Message :\n" . ($request->message ?? "Aucun message.") . "\n\n" .
            "Traitez cette demande dans votre espace de gestion.";
    }
}
