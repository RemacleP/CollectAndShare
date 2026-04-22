<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request, $id = null): Response
    {
        // Si un ID est fourni, on cherche cet utilisateur, sinon on prend l'utilisateur connecté
        $user = $id
            ? User::with(['address', 'shippingAddress', 'billingAddress'])->findOrFail($id)
            : $request->user()->load(['address', 'shippingAddress', 'billingAddress']);

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'isOwnProfile' => $request->user()->id === $user->id,
            // On envoie la liste des conversations de l'utilisateur connecté
            'myConversations' => auth()->user()->conversations()
                ->select('conversations.id', 'title', 'slug')
                ->whereDoesntHave('users', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->get(),
        ]);
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $request->user()->load(['address', 'shippingAddress', 'billingAddress']),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

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

        // On utilise addresses() au pluriel pour chercher dans toutes les lignes de l'user
        $request->user()->addresses()->updateOrCreate(
            ['type' => $validated['type']], // On cherche par type (ex: shipping)
            $validated                      // On met à jour ou on crée
        );

        return back();
    }

    /**
     * Delete the user's account.
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

    public function index()
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
}
