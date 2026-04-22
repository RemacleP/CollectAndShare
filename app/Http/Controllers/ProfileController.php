<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialPlatform;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Affiche le profil d'un utilisateur (Public ou personnel).
     */
    public function show(Request $request, $id = null): Response
    {
        // On charge les adresses ET les réseaux sociaux avec leurs plateformes
        $user = $id
            ? User::with(['address', 'shippingAddress', 'billingAddress', 'socialLinks.platform'])->findOrFail($id)
            : $request->user()->load(['address', 'shippingAddress', 'billingAddress', 'socialLinks.platform']);

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'socials' => $user->socialLinks,
            'isOwnProfile' => $request->user() ? $request->user()->id === $user->id : false,
            // Liste des conversations de l'utilisateur connecté pour l'invitation
            'myConversations' => $request->user()
                ? $request->user()->conversations()
                    ->select('conversations.id', 'title', 'slug')
                    ->whereDoesntHave('users', function($query) use ($user) {
                        $query->where('users.id', $user->id);
                    })
                    ->get()
                : [],
        ]);
    }

    /**
     * Formulaire d'édition du profil.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $request->user()->load(['address', 'shippingAddress', 'billingAddress', 'socialLinks.platform']),
            'social_platforms' => SocialPlatform::where('is_active', true)->get(),
        ]);
    }

    /**
     * Mise à jour des informations du profil et des réseaux sociaux.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }


    public function updateSocials(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'social_links' => 'nullable|array',
            'social_links.*.platform_id' => 'required|exists:social_platforms,id',
            'social_links.*.identifier' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($user, $validated) {
            // Suppression et recréation (logique identique au Club)
            $user->socialLinks()->delete();

            if (!empty($validated['social_links'])) {
                foreach ($validated['social_links'] as $link) {
                    if (!empty($link['identifier'])) {
                        // 1. On récupère la plateforme pour avoir sa base_url
                        $platform = \App\Models\SocialPlatform::find($link['platform_id']);

                        // 2. On construit l'URL complète
                        // Si la base_url existe, on concatène, sinon on prend l'identifiant tel quel
                        $fullUrl = $platform->base_url
                            ? $platform->base_url . ltrim($link['identifier'], '@/')
                            : $link['identifier'];

                        $user->socialLinks()->create([
                            'social_platform_id' => $link['platform_id'],
                            'identifier'         => $link['identifier'],
                            'full_url'           => $fullUrl,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Réseaux sociaux mis à jour.');
    }

    /**
     * Mise à jour spécifique de l'adresse (via AJAX/Inertia).
     */
    public function updateAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:primary,shipping,billing',
            'street' => 'required|string',
            'number' => 'required|string',
            'box' => 'nullable|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
        ]);

        $request->user()->addresses()->updateOrCreate(
            ['type' => $validated['type']],
            $validated
        );

        return back()->with('success', 'Adresse mise à jour.');
    }

    /**
     * Suppression du compte.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Liste des utilisateurs pour l'administration.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->when(request('search'), function ($q, $search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('lastname')
                ->paginate(10)
                ->withQueryString(),
            'filters' => request()->only(['search'])
        ]);
    }

    public function updateTheme(Request $request)
    {
        // 1. Validation stricte
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
        ]);

        // 2. Mise à jour de l'utilisateur connecté
        $user = $request->user();

        // On peut utiliser update() qui sauve immédiatement si le champ est fillable
        $user->update([
            'theme' => $validated['theme']
        ]);

        // 3. Retour vers la page précédente (Inertia rafraîchira les données)
        return back();
    }
}
