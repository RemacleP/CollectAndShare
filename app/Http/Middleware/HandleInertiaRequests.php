<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\CartItem;

class HandleInertiaRequests extends Middleware
{
    /**
     * Le chemin vers la vue racine (blade) qui est chargée au premier accès.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Détermine la version actuelle de l'asset.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Définit les données qui sont partagées par défaut avec Inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            // Authentification et Utilisateur
            'auth' => [
                'user' => $user ? [
                    'id'        => $user->id,
                    'firstname' => $user->firstname,
                    'lastname'  => $user->lastname,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'theme' => $request->user()->theme,
                    // Utilise ta méthode de modèle pour le check admin
                    'is_admin'  => $user->isSuperAdmin(),
                    // Récupération des IDs de clubs pour les accès rapides
                    'club_ids'  => $user->clubs->pluck('id')->toArray(),
                    // Rôles pour la gestion des permissions côté Vue
                    'roles'     => $user->roles->pluck('name'),
                ] : null,
            ],

            // Gestion globale du panier (Badge Navbar)
            'cartCount' => $user
                ? (int) CartItem::where('user_id', $user->id)->sum('quantity')
                : 0,

            // Textes légaux (évite de les stocker en dur dans les composants Vue)
            'legal' => [
                'protection_donnees' => [
                    'collecte_donnees' => [
                        'inscription' => "Les informations recueillies lors de votre inscription sont nécessaires pour la gestion de votre compte.",
                    ],
                ],
                'mentions' => [
                    'editeur' => "Collect & Share asbl",
                    'contact' => "contact@collectandshare.be",
                ]
            ],

            // logo de plateforme
            'platform' => [
                'logo' => \App\Models\Setting::where('key', 'site_logo')->value('value') ?? '/images/logo-default.svg',
            ],

            // Messages Flash (Notifications)
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'info'    => fn () => $request->session()->get('info'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            'ziggy' => [
                'location' => $request->url(),
                'query' => $request->query(),
            ],
        ];
    }
}
