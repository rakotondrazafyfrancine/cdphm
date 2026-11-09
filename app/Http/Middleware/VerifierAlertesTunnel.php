<?php

namespace App\Http\Middleware;

use App\Services\AlerteTunnel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifierAlertesTunnel
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // pas plus d'une vérification toutes les 5 minutes, même avec le rafraîchissement automatique
            if (Cache::add('alertes_tunnel_verifiees', true, 300)) {
                AlerteTunnel::verifier();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $next($request);
    }
}
