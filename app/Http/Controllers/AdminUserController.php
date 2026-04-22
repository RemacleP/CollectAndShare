<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        // On s'assure de prendre TOUS les champs nécessaires pour la Vue
        $users = User::query()
            ->select('id', 'username', 'firstname', 'lastname', 'email', 'id_verified_at', 'is_admin', 'is_banned')
            ->when(request('search'), function ($q, $search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('lastname', 'asc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search'])
        ]);
    }

    public function verify(User $user)
    {
        $user->update(['id_verified_at' => now()]);
        return back()->with('success', "Le profil de {$user->firstname} a été validé.");
    }

    public function toggleBan(User $user)
    {
        if ($user->id === 1) {
            abort(403, "Action impossible sur le compte racine.");
        }

        $user->update(['is_banned' => !$user->is_banned]);

        $msg = $user->is_banned ? "banni" : "réactivé";
        return back()->with('success', "Le compte de {$user->firstname} a été {$msg}.");
    }
}
