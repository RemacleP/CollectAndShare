<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        // On récupère l'utilisateur connecté
        $user = $request->user();

        // Si l'utilisateur est connecté ET qu'il est banni
        if ($user && $user->is_banned) {

            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été suspendu par l\'administration.',
            ]);
        }

        return $next($request);
    }
}
