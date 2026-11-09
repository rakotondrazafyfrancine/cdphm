<?php

namespace App\Http\Controllers;

use App\Models\SortieChambre;
use App\Models\SortieTunnel;

use Illuminate\Http\Request;

class FactureWebController extends Controller
{
   public function index()
{
    $facturesChambre = SortieChambre::with('lot.client')->get()->map(function($s) {
        $s->type = 'chambre';
        return $s;
    });

    $facturesTunnel = SortieTunnel::with('lot.client')->get()->map(function($s) {
        $s->type = 'tunnel';
        return $s;
    });

    $factures = $facturesChambre->concat($facturesTunnel)->sortByDesc('created_at')->values();

    return view('factures.index', compact('factures'));
}
}
