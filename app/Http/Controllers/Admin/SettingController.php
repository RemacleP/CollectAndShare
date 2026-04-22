<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', [
            'settings' => Setting::all()->pluck('value', 'key')
        ]);
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Optionnel : supprimer l'ancien logo pour ne pas encombrer le serveur
            $oldLogo = Setting::where('key', 'site_logo')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo->value));
            }

            // Sauvegarder le nouveau
            $path = $request->file('logo')->store('platform', 'public');

            Setting::updateOrCreate(
                ['key' => 'site_logo'],
                ['value' => '/storage/' . $path]
            );
        }

        return back()->with('success', 'Logo mis à jour avec succès.');
    }
}
