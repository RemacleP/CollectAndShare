<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use App\Models\ClubUser;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $perPage = $request->input('perPage', 10);

        $query = Category::query()->whereHas('owners', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'name';
        $query->orderBy($sort, $direction);

        $categories = $query->paginate($perPage)->withQueryString();

        return Inertia::render('categories/Index', [
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => (int) $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);
        $clubUser = ClubUser::where('user_id', auth()->id())->first();

        if ($clubUser) {
            $category->owners()->attach($clubUser->id);
        }



        return Redirect::back()->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return Redirect::back()->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {

        $category->delete();

        return Redirect::back()->with('success', 'Catégorie supprimée avec succès.');
    }
}
