<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Role;
use App\Http\Resources\ClubResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClubController extends Controller
{
    /**
     * Liste des clubs avec recherche et pagination.
     */
    public function index(Request $request)
    {
        $clubs = Club::query()
            ->with(['address'])
            ->withCount('users')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy($request->input('sort', 'name'), $request->input('direction', 'asc'))
            ->paginate($request->input('perPage', 9))
            ->withQueryString();

        return Inertia::render('Clubs/Index', [
            'clubs' => ClubResource::collection($clubs),
            'filters' => $request->only(['search', 'sort', 'direction', 'perPage'])
        ]);
    }

    /**
     * Formulaire de création d'un club.
     */
    public function create()
    {
        return Inertia::render('Clubs/Create');
    }

    /**
     * Détails d'un club (Page Show).
     */
    public function show(string $slug)
    {
        // On ajoute 'events' à la liste des relations chargées
        $club = Club::where('slug', $slug)
            ->with(['address', 'users.roles', 'events' => function($query) {
                $query->where('status', 'validated') // Optionnel : ne montrer que les validés
                ->orderBy('start_datetime', 'asc');
            }])
            ->withCount('users')
            ->firstOrFail();

        $authUser = Auth::user();

        return Inertia::render('Clubs/Show', [
            'club' => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'email' => $club->email,
                'phone' => $club->phone,
                'logo' => $club->logo ? asset('storage/' . $club->logo) : null,
                'address' => $club->address,
                'members_count' => $club->users_count,

                // On mappe les événements pour le composant Vue
                'events' => $club->events->map(fn($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'slug' => $e->slug,
                    'start_datetime' => $e->start_datetime,
                    'end_datetime' => $e->end_datetime,
                    'city' => $e->city,
                    'location_name' => $e->location_name,
                    'image' => $e->image ? asset('storage/' . $e->image) : null,
                ]),

                'members' => $club->users->map(fn($u) => [
                    'id' => $u->id,
                    'firstname' => $u->firstname,
                    'lastname' => $u->lastname,
                    'full_name' => "{$u->firstname} {$u->lastname}",
                    'username' => $u->username,
                    'is_super_admin' => (bool) $u->is_admin,
                    'club_role' => $u->roles->firstWhere('id', $u->pivot->role_id)?->label ?? 'Membre',
                ]),
            ],
            'can' => [
                'edit' => $authUser ? (
                    $authUser->is_admin ||
                    $authUser->roles()->where('club_id', $club->id)->where('name', 'responsable')->exists()
                ) : false,
            ]
        ]);
    }

    /**
     * Enregistrement d'un nouveau club.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'box' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $logoPath = $request->file('logo') ? $request->file('logo')->store('clubs/logos', 'public') : null;

            $club = Club::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'logo' => $logoPath,
            ]);

            $club->address()->create([
                'street' => $validated['street'],
                'number' => $validated['number'],
                'box' => $validated['box'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'country' => $validated['country'],
            ]);

            // Le créateur devient "Président" (ID 2 dans ta table roles)
            $presidentRole = Role::where('name', 'president')->first();
            if ($presidentRole) {
                $club->users()->attach(Auth::id(), ['role_id' => $presidentRole->id]);
            }

            return redirect()->route('clubs.show', $club->slug)
                ->with('success', 'Le club a été créé avec succès !');
        });
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Club $club)
    {
        // Vérifie si l'utilisateur a le droit (via Policy ou manuellement)
        $user = Auth::user();
        $hasAccess = $user->is_admin || $user->roles()->where('club_id', $club->id)->where('name', 'president')->exists();

        if (!$hasAccess) {
            abort(403);
        }

        return Inertia::render('Clubs/Edit', [
            'club' => $club->load('address')
        ]);
    }

    /**
     * Mise à jour du club.
     */
    public function update(Request $request, Club $club)
    {
        $user = Auth::user();
        $hasAccess = $user->is_admin || $user->roles()->where('club_id', $club->id)->where('name', 'president')->exists();

        if (!$hasAccess) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'box' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $validated, $club) {
            if ($request->hasFile('logo')) {
                if ($club->logo) {
                    Storage::disk('public')->delete($club->logo);
                }
                $club->logo = $request->file('logo')->store('clubs/logos', 'public');
            }

            $club->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            $club->address()->updateOrCreate(
                ['addressable_id' => $club->id, 'addressable_type' => Club::class],
                [
                    'street' => $validated['street'],
                    'number' => $validated['number'],
                    'box' => $validated['box'],
                    'postal_code' => $validated['postal_code'],
                    'city' => $validated['city'],
                    'country' => $validated['country'],
                ]
            );
        });

        return redirect()->route('clubs.show', $club->slug)
            ->with('success', 'Le club a été mis à jour avec succès !');
    }
}
