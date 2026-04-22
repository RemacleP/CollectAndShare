<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AdminClubController extends Controller
{
    public function index(Request $request)
    {
        // On récupère tout, même les clubs archivés (Soft Deleted)
        $clubs = Club::withTrashed()
            ->with(['address'])
            ->withCount('users')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(15);

        return Inertia::render('Admin/Clubs/Index', [
            'clubs' => $clubs,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Restaurer un club archivé
     */
    public function restore($id)
    {
        $club = Club::withTrashed()->findOrFail($id);
        $club->restore();

        return back()->with('success', 'Club restauré avec succès.');
    }

    /**
     * Suppression définitive (Nettoyage base de données)
     */
    public function forceDelete($id)
    {
        $club = Club::withTrashed()->findOrFail($id);

        // Ici on supprime VRAIMENT tout
        if ($club->logo) \Storage::disk('public')->delete($club->logo);
        $club->forceDelete();

        return back()->with('success', 'Club supprimé définitivement de la base de données.');
    }
}
