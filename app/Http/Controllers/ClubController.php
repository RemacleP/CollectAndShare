<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Role;
use App\Models\SocialPlatform;
use App\Http\Resources\ClubResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    /**
     * Liste des clubs.
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
     * Formulaire de création.
     */
    public function create()
    {
        return Inertia::render('Clubs/Create', [
            'social_platforms' => SocialPlatform::where('is_active', true)->get()
        ]);
    }

    /**
     * Détails d'un club.
     */
    public function show(string $slug)
    {
        $club = Club::where('slug', $slug)
            ->with(['address', 'users.roles', 'conversations', 'socialLinks.platform', 'events' => function($query) {
                $query->where('status', 'validated')
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

                // Formatage des liens sociaux
                'socials' => $club->socialLinks->map(fn($link) => [
                    'name' => $link->platform->name,
                    'icon' => $link->platform->icon,
                    'url' => $link->platform->base_url . $link->identifier,
                ]),

                'events' => $club->events->map(fn($e) => [
                    'id' => $e->id, 'title' => $e->title, 'slug' => $e->slug,
                    'start_datetime' => $e->start_datetime, 'end_datetime' => $e->end_datetime,
                    'city' => $e->city, 'location_name' => $e->location_name,
                    'image' => $e->image ? asset('storage/' . $e->image) : null,
                ]),

                'members' => $club->users->map(fn($u) => [
                    'id'        => $u->id,
                    'firstname' => $u->firstname,
                    'lastname'  => $u->lastname,
                    'username'  => $u->username,
                    'club_role' => $u->roles->firstWhere('id', $u->pivot->role_id)?->label ?? 'Membre',
                    'is_super_admin' => $u->is_admin, // Pour le badge "Staff"
                ]),
                'conversations' => $club->conversations->map(fn($c) => [
                    'id' => $c->id, 'title' => $c->title, 'slug' => $c->slug,
                ]),
            ],
            'can' => [
                'edit' => $authUser ? (
                    $authUser->is_admin || $authUser->roles()->where('club_id', $club->id)->where('name', 'responsable')->exists()
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
            'social_links' => 'nullable|array', // Changement : attend un tableau d'objets
            'social_links.*.platform_id' => 'required|exists:social_platforms,id',
            'social_links.*.identifier' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $logoPath = $request->file('logo') ? $request->file('logo')->store('clubs/logos', 'public') : null;
            //Génération des slug + préfixe aléatoires 10
            $slug = Str::slug($validated['name']) . '-' . strtolower(Str::random(10));
            $club = Club::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'slug' => $slug,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'logo' => $logoPath,
            ]);

            $club->address()->create($request->only(['street', 'number', 'box', 'postal_code', 'city', 'country']));

            // Gestion des réseaux sociaux (Multi-liens)
            if (!empty($validated['social_links'])) {
                foreach ($validated['social_links'] as $link) {
                    if (!empty($link['identifier'])) {
                        $club->socialLinks()->create([
                            'social_platform_id' => $link['platform_id'],
                            'identifier' => $link['identifier']
                        ]);
                    }
                }
            }

            $responsableRole = Role::where('name', 'responsable')->first();
            if ($responsableRole) {
                $club->users()->attach(Auth::id(), ['role_id' => $responsableRole->id]);
            }

            return redirect()->route('clubs.show', $club->slug)->with('success', 'Club créé !');
        });
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Club $club)
    {
        $user = Auth::user();
        if (!$user->is_admin && !$user->roles()->where('club_id', $club->id)->where('name', 'responsable')->exists()) {
            abort(403);
        }

        return Inertia::render('Clubs/Edit', [
            'club' => $club->load(['address', 'socialLinks']),
            'social_platforms' => SocialPlatform::where('is_active', true)->get()
        ]);
    }

    /**
     * Mise à jour du club.
     */
    public function update(Request $request, Club $club)
    {
        $user = Auth::user();
        if (!$user->is_admin && !$user->roles()->where('club_id', $club->id)->where('name', 'responsable')->exists()) {
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
            'social_links' => 'nullable|array',
            'social_links.*.platform_id' => 'required|exists:social_platforms,id',
            'social_links.*.identifier' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $validated, $club) {
            if ($request->hasFile('logo')) {
                if ($club->logo) Storage::disk('public')->delete($club->logo);
                $club->logo = $request->file('logo')->store('clubs/logos', 'public');
            }

            $club->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'logo' => $club->logo,
            ]);

            $club->address()->updateOrCreate(
                ['addressable_id' => $club->id, 'addressable_type' => Club::class],
                $request->only(['street', 'number', 'box', 'postal_code', 'city', 'country'])
            );

            // LOGIQUE DE SYNCHRONISATION MULTI-LIENS
            // On supprime les anciens liens et on réinsère le nouveau tableau
            $club->socialLinks()->delete();

            if (!empty($validated['social_links'])) {
                foreach ($validated['social_links'] as $link) {
                    if (!empty($link['identifier'])) {
                        $club->socialLinks()->create([
                            'social_platform_id' => $link['platform_id'],
                            'identifier' => $link['identifier']
                        ]);
                    }
                }
            }
        });

        return redirect()->route('clubs.show', $club->slug)->with('success', 'Club mis à jour !');
    }

    /**
     * API pour liste simple.
     */
    public function apiIndex()
    {
        $clubs = Club::with('users.roles')->get()->map(function($club) {
            $hasManager = $club->users->contains(fn($user) =>
            $user->roles->contains(fn($role) => mb_strtolower(trim($role->name)) === 'responsable')
            );

            return [
                'id' => $club->id,
                'name' => $club->name,
                'has_manager' => $hasManager,
            ];
        });

        return response()->json(['clubs' => $clubs]);
    }
}
