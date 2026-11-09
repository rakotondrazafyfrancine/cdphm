<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    /**
     * Liste complète de l'activité récente : tous les lots encore présents
     * (en congélation ou en chambre). Les lots sortis n'apparaissent pas.
     * Page : /activite
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $statut = $request->input('statut');

        $lots = Lot::with('client')
            ->where('statut', '!=', 'sorti')
            ->when(
                in_array($statut, ['en_stock', 'en_congelation'], true),
                fn($query) => $query->where('statut', $statut)
            )
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('espece', 'like', "%{$q}%")
                        ->orWhereHas('client', fn($c) => $c->where('nom', 'like', "%{$q}%"));

                    // Recherche par numéro : "LOT-0019" ou "19"
                    $num = (int) preg_replace('/\D/', '', $q);
                    if ($num > 0) {
                        $sub->orWhere('id', $num);
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('activite.index', compact('lots', 'q', 'statut'));
    }
}
