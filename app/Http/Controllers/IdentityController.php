<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IdentityController extends Controller
{
    public function upload(Request $request)
    {
        // 1. Validation de l'image
        $request->validate([
            'photo' => 'required|image|max:2048', // Max 2Mo
        ]);

        $user = Auth::user();

        // 2. Stockage de l'image
        // On la place dans 'public/profiles' pour qu'elle soit accessible
        if ($request->file('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');

            // Si l'utilisateur avait déjà une photo, on peut supprimer l'ancienne ici (optionnel)
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
        }

        // 3. Génération du numéro e-ID (Simulation du scan)
        $fakeEid = rand(100, 999) . '-' . rand(1000000, 9999999) . '-' . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);

        // 4. Mise à jour de l'utilisateur
        $user->update([
            'eid_number' => $fakeEid,
            'profile_photo_path' => $path ?? $user->profile_photo_path,
            'id_verified_at' => now(),
        ]);

        return back()->with('status', 'Votre identité a été certifiée avec succès à partir de la photo eID Viewer !');
    }
}
