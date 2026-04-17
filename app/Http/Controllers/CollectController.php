<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequest;
use App\Http\Requests\ElementRequest;
use App\Models\Collection;
use App\Models\Element;
use App\Models\Club;
use App\Models\Category;
use App\Models\ClubUserRole; // On utilise uniquement celui-ci
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CollectController extends Controller
{
    use AuthorizesRequests;

    public function listeCollec(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;

        $myCategories = Category::orderBy('name')->get();

        // 1. On commence par une requête simple
        $query = Collection::query();

        // 2. On charge les relations en "Eager Loading" (LEFT JOIN automatique de Laravel)
        // On retire club_user_role.user du with principal si c'est lui qui bloque
        $query->with(['club', 'categories']);

        // 3. Filtrage par catégorie (si demandé)
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category));
        }

        // 4. On récupère les collections
        $collections = $query->latest()->get()->map(function ($collection) use ($user) {
            // On charge la relation utilisateur manuellement ici pour éviter de casser la requête principale
            $collection->load('club_user_role.user');

            // Correction de la Policy : on passe false si pas d'user
            $collection->can_edit = $user ? $user->can('update', $collection) : false;

            return $collection;
        });

        return Inertia::render('collections/listeCollec', [
            'collects' => $collections,
            'categories' => $myCategories,
            'filters' => $request->only(['category']),
            'open' => $request->open,
            'userId' => $userId,
            'isAdmin' => $user?->is_admin ?? false,
        ]);
    }

    public function createCollec()
    {
        $user = auth()->user();

        return Inertia::render('collections/createCollec', [
            'clubs' => Club::all(),
            'club_users' => ClubUserRole::with('user')->get(),
            'categories' => Category::all(),
            'userClub' => $user->clubs()->first(),
            'userId' => $user->id,
            'isUser' => true,
            'isClubManager' => (bool) $user->is_admin, // Correction ici
        ]);
    }

    public function storeQuick(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'is_active' => true
        ]);

        return response()->json([
            'category' => $category,
            'message' => 'Catégorie ajoutée !'
        ]);
    }

    public function storeCollec(CollectionRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        // Récupération du lien Pivot via club_user_role
        // On cherche la ligne qui lie cet utilisateur à ce club
        $pivot = ClubUserRole::where('club_id', $data['club_id'])
            ->where('user_id', $user->id)
            ->first();

        // Si l'utilisateur n'est pas dans le club et n'est pas admin, on bloque
        if (!$pivot && !$user->is_admin) {
            return back()->withErrors(['club_id' => 'Vous devez appartenir à ce club pour y créer une collection.']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('collections', 'public');
        }

        // Si c'est un admin qui crée mais qu'il n'est pas dans le club,
        // on prend l'ID envoyé par le formulaire (si spécifié) ou null
        $clubUserId = $pivot ? $pivot->id : ($data['club_user_id'] ?? null);

        $collection = Collection::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'image' => $data['image'] ?? null,
            'club_id' => $data['club_id'],
            'club_user_id' => $clubUserId,
            'slug' => \Illuminate\Support\Str::slug($data['name']) . '-' . rand(100, 999),
        ]);

        if ($request->has('categories')) {
            $collection->categories()->sync($request->categories);
        }

        return redirect()->route('collections.listeCollec')->with('success', "Collection créée !");
    }

    public function editCollec(Collection $currentCollect)
    {
        // Attention : Vérifie que ta Policy CollectionPolicy est à jour avec club_user_role
        $this->authorize('update', $currentCollect);

        $user = auth()->user();
        $currentCollect->load(['club', 'club_user_role.user', 'categories']);

        return Inertia::render('collections/editCollec', [
            'collect' => $currentCollect,
            'clubs' => $user->is_admin ? Club::all() : $user->clubs,
            'categories' => Category::all(),
        ]);
    }


    public function updateCollec(CollectionRequest $request, Collection $currentCollect)
    {
        $this->authorize('update', $currentCollect);

        $data = $request->validated();

        if ($request->boolean('delete_image')) {
            if ($currentCollect->image) Storage::disk('public')->delete($currentCollect->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($currentCollect->image) Storage::disk('public')->delete($currentCollect->image);
            $data['image'] = $request->file('image')->store('collections', 'public');
        }

        $currentCollect->update($data);
        $currentCollect->categories()->sync($request->input('categories', []));

        return redirect()->route('collections.listeCollec')->with('success', "Collection mise à jour !");
    }

    public function deleteCollec(Collection $currentCollect)
    {
        $this->authorize('delete', $currentCollect);

        if ($currentCollect->image) Storage::disk('public')->delete($currentCollect->image);
        $currentCollect->delete();

        return redirect()->route('collections.listeCollec')->with('success', "Collection supprimée.");
    }

    // --- ELEMENTS ---

    public function listeElem(Collection $currentCollect, Request $request)
    {
        $currentCollect->load(['elements', 'club_user.user', 'club']);

        $user = auth()->user();
        $canManage = $user ? $user->can('update', $currentCollect) : false;

        return Inertia::render('elements/listeElem', [
            'collect' => $currentCollect,
            'elements' => $currentCollect->elements,
            'canManage' => $canManage,
            'userId' => auth()->id(),
        ]);
    }

    public function createElem(Collection $currentCollect)
    {
        $this->authorize('update', $currentCollect);

        return Inertia::render('elements/createElem', [
            'collect' => $currentCollect,
        ]);
    }

    public function storeElem(ElementRequest $request, Collection $currentCollect)
    {
        $this->authorize('update', $currentCollect);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('elements', 'public');
        }

        $currentCollect->elements()->create($data);

        return redirect()->route('elements.listeElem', $currentCollect->slug)
            ->with('success', "Élément ajouté !");
    }

    public function updateElem(ElementRequest $request, Collection $currentCollect, Element $currentElem)
    {
        $this->authorize('update', $currentCollect);

        $data = $request->validated();

        if ($request->boolean('delete_image')) {
            if ($currentElem->image) Storage::disk('public')->delete($currentElem->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($currentElem->image) Storage::disk('public')->delete($currentElem->image);
            $data['image'] = $request->file('image')->store('elements', 'public');
        }

        $currentElem->update($data);

        return redirect()->route('elements.listeElem', $currentCollect->slug)
            ->with('success', "Élément modifié !");
    }

    public function deleteElem(Collection $currentCollect, Element $currentElem)
    {
        $this->authorize('update', $currentCollect);

        if ($currentElem->image) Storage::disk('public')->delete($currentElem->image);
        $currentElem->delete();

        return redirect()->route('elements.listeElem', $currentCollect->slug)
            ->with('success', "Élément supprimé.");
    }
}
