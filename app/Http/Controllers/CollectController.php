<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequest;
use App\Http\Requests\ElementRequest;
use App\Models\Collection;
use App\Models\Element;
use App\Models\Club;
use App\Models\Category;
use App\Models\ClubUserRole;
use App\Models\ElementImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CollectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Liste toutes les collections (Vue publique/membre)
     */
    public function listeCollec(Request $request)
    {
        $user = auth()->user();
        $myCategories = Category::orderBy('name')->get();

        $query = Collection::query()->with(['club', 'categories']);

        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category));
        }

        $collections = $query->latest()->get()->map(function ($collection) use ($user) {
            $collection->load('club_user_role.user');
            // La Policy gère l'accès Propriétaire OU Admin automatiquement
            $collection->can_edit = $user ? $user->can('update', $collection) : false;
            return $collection;
        });

        return Inertia::render('collections/listeCollec', [
            'collects' => $collections,
            'categories' => $myCategories,
            'filters' => $request->only(['category']),
            'open' => $request->open,
            'userId' => $user?->id,
            'isAdmin' => $user?->is_admin ?? false,
        ]);
    }

    /**
     * Formulaire de création de collection
     */
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
            'isClubManager' => (bool) $user->is_admin,
        ]);
    }

    /**
     * Ajout rapide de catégorie via AJAX/JSON
     */
    public function storeQuick(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => true
        ]);

        return response()->json([
            'category' => $category,
            'message' => 'Catégorie ajoutée !'
        ]);
    }

    /**
     * Enregistre une nouvelle collection
     */
    public function storeCollec(CollectionRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        $pivot = ClubUserRole::where('club_id', $data['club_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot && !$user->is_admin) {
            return back()->withErrors(['club_id' => 'Vous devez appartenir à ce club pour y créer une collection.']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('collections', 'public');
        }

        $clubUserId = $pivot ? $pivot->id : ($data['club_user_id'] ?? null);

        $collection = Collection::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'image' => $data['image'] ?? null,
            'club_id' => $data['club_id'],
            'club_user_id' => $clubUserId,
            'slug' => Str::slug($data['name']) . '-' . rand(100, 999),
        ]);

        if ($request->has('categories')) {
            $collection->categories()->sync($request->categories);
        }

        return redirect()->route('collections.listeCollec')->with('success', "Collection créée !");
    }

    /**
     * Formulaire d'édition d'une collection
     */
    public function editCollec(Collection $collection)
    {
        $this->authorize('update', $collection);

        $user = auth()->user();
        $collection->load(['club', 'club_user_role.user', 'categories']);

        return Inertia::render('collections/editCollec', [
            'collect' => $collection,
            'clubs' => $user->is_admin ? Club::all() : $user->clubs,
            'categories' => Category::all(),
            'club_users' => ClubUserRole::with('user')->get(),
            'isUser' => $user->id === $collection->club_user_role?->user_id,
            'isClubManager' => (bool) $user->is_admin,
        ]);
    }

    /**
     * Mise à jour d'une collection
     */
    public function updateCollec(CollectionRequest $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $data = $request->validated();

        if ($request->boolean('delete_image')) {
            if ($collection->image) Storage::disk('public')->delete($collection->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($collection->image) Storage::disk('public')->delete($collection->image);
            $data['image'] = $request->file('image')->store('collections', 'public');
        } else {
            unset($data['image']);
        }

        $collection->update($data);
        $collection->categories()->sync($request->input('categories', []));

        return redirect()->route('collections.listeCollec')->with('success', "Collection mise à jour !");
    }

    /**
     * Suppression d'une collection
     */
    public function deleteCollec(Collection $collection)
    {
        $this->authorize('delete', $collection);

        if ($collection->image) Storage::disk('public')->delete($collection->image);
        $collection->delete();

        return redirect()->route('collections.listeCollec')->with('success', "Collection supprimée.");
    }

    // --- GESTION DES ÉLÉMENTS (OBJETS) ---

    /**
     * Liste des éléments d'une collection spécifique
     */
    public function listeElem(Collection $collection, Request $request)
    {
        $collection->load(['elements.images', 'club_user_role.user', 'club']);

        return Inertia::render('elements/listeElem', [
            'collect' => $collection,
            'elements' => $collection->elements,
            'userId' => auth()->id(),
            'collectionOwnerUserId' => $collection->club_user_role?->user_id,
            'isAdmin' => auth()->user()?->is_admin ?? false,
        ]);
    }

    /**
     * Formulaire de création d'un élément
     */
    public function createElem(Collection $collection)
    {
        $this->authorize('update', $collection);
        return Inertia::render('elements/createElem', ['collect' => $collection]);
    }

    /**
     * Enregistre un nouvel élément dans la collection
     */
    public function storeElem(ElementRequest $request, Collection $collection)
    {
        $this->authorize('update', $collection);
        $data = $request->validated();
        //dd($data);
        $data['slug'] = Str::slug($data['label']) . '-' . uniqid();
        $element = $collection->elements()->create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('elements', 'public');
                $element->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('elements.listeElem', $collection->slug)
            ->with('success', "Élément ajouté !");
    }

    /**
     * Affiche un élément précis (Show)
     */
    public function showElem(Collection $collection, Element $element)
    {
        $user = auth()->user();
        $element->load(['images', 'collection.club_user_role']);

        return Inertia::render('elements/showElem', [
            'collect' => $collection,
            'element' => $element,
            'can_edit' => $user ? $user->can('update', $collection) : false,
        ]);
    }

    /**
     * Formulaire d'édition d'un élément
     */
    public function editElem(Collection $collection, Element $element)
    {
        $this->authorize('update', $collection);
        $element->load('images');

        return Inertia::render('elements/editElem', [
            'collect' => $collection,
            'element' => $element,
        ]);
    }

    /**
     * Mise à jour d'un élément
     */
    public function updateElem(ElementRequest $request, Collection $collection, Element $element)
    {
        $this->authorize('update', $collection);
        $data = $request->validated();

        $element->update($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('elements', 'public');
                $element->images()->create(['path' => $path]);
            }
        }

        // Si tu as une logique de suppression d'images spécifiques (ElementImage)
        if ($request->has('remove_images')) {
            $imagesToDelete = \App\Models\ElementImage::whereIn('id', $request->remove_images)
                ->where('element_id', $element->id)
                ->get();
            foreach ($imagesToDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        return redirect()->route('elements.listeElem', $collection->slug)
            ->with('success', "Élément mis à jour !");
    }

    /**
     * Suppression d'un élément
     */
    public function deleteElem(Collection $collection, Element $element)
    {
        $this->authorize('update', $collection);

        $element->load('images');
        foreach ($element->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        $element->delete();

        return redirect()->route('elements.listeElem', $collection->slug)
            ->with('success', "Élément supprimé.");
    }
}
