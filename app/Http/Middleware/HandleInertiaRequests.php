<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Cart;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cartCount = 0;
        $user = $request->user();

        // Gestion du panier
        if ($user) {
            $cart = Cart::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($cart) {
                $cartCount = $cart->elements()->count();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'username' => $user->username,
                    'email' => $user->email,
                    // Utilisation de la méthode de ton modèle User.php
                    // C'est cette clé 'is_admin' que ton JS va lire
                    'is_admin' => $user->isSuperAdmin(),

                    'club_ids' => $user->clubs->pluck('id')->toArray(),
                    'roles' => $user->roles->pluck('name'), // Plus simple : renvoie ['admin', 'editor']
                ] : null,
            ],

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

            'cartCount' => $cartCount,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'info'    => fn () => $request->session()->get('info'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
