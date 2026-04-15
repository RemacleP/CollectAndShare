<?php

namespace App\Http\Controllers;

use App\Models\LienUtile;
use App\Services\LegalServices;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Vérifie si l'utilisateur actuel est administrateur.
     */
    private function isAdmin(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    /**
     * Affiche les mentions légales.
     */
    public function showLegal(LegalServices $service): Response
    {
        return Inertia::render('legals/mentionsLegales', [
            'legal' => $service->getLegalContent(),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    /**
     * Met à jour le contenu des mentions légales (JSON).
     */
    public function updateLegal(Request $request, LegalServices $service)
    {
        // 1. Vérifie si l'utilisateur est bien admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'legal' => 'required|array'
        ]);

        $service->updateLegalContent($validated['legal']);

        return back()->with('success', 'Mentions légales mises à jour avec succès.');
    }

    /**
     * Affiche la page contact.
     */
    public function showContact(): Response
    {
        return Inertia::render('legals/contacts');
    }

    /**
     * Liste les liens utiles.
     */
    public function showLiens(): Response
    {
        return Inertia::render('liensUtiles/index', [
            'liensUtiles' => LienUtile::all(),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    /**
     * Formulaire de création de lien utile.
     */
    public function createLiens(): Response
    {
        abort_unless($this->isAdmin(), 403);

        return Inertia::render('liensUtiles/create');
    }

    /**
     * Enregistre un nouveau lien utile.
     */
    public function storeLiens(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        LienUtile::create($validated);

        return redirect()->route('liensUtiles.index')->with('success', 'Lien utile créé avec succès.');
    }

    /**
     * Formulaire d'édition de lien utile.
     */
    public function editLiens(LienUtile $lienUtile): Response
    {
        abort_unless($this->isAdmin(), 403);

        return Inertia::render('liensUtiles/edit', [
            'lienUtile' => $lienUtile,
        ]);
    }

    /**
     * Met à jour un lien utile.
     */
    public function updateLiens(Request $request, LienUtile $lienUtile)
    {
        abort_unless($this->isAdmin(), 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        $lienUtile->update($validated);

        return redirect()->route('liensUtiles.index')->with('success', 'Lien utile mis à jour.');
    }

    /**
     * Supprime un lien utile.
     */
    public function deleteLiens(LienUtile $lienUtile)
    {
        abort_unless($this->isAdmin(), 403);

        $lienUtile->delete();

        return redirect()->route('liensUtiles.index')->with('success', 'Lien utile supprimé.');
    }
}
