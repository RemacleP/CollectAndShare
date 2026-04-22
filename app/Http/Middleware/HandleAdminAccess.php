<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Autoriser Pascal (SuperAdmin)
        if ($user && $user->is_admin) {
            return $next($request);
        }

        // 2. Autoriser l'Admin Plateforme (si tu as une table/rôle dédié)
        // Si tu n'as pas encore de rôles, cette ligne suffira pour Pascal

        abort(403, "Accès réservé à l'administration.");
    }
}
