<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChambreFroide;
use Illuminate\Http\Request;

class ChambreFroideController extends Controller
{
    public function index()
    {
        return response()->json(ChambreFroide::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'capacite_tonnes' => 'required|numeric',
        ]);

        return response()->json(ChambreFroide::create($request->all()), 201);
    }

    public function show(ChambreFroide $chambreFroide)
    {
        return response()->json($chambreFroide);
    }

    public function update(Request $request, ChambreFroide $chambreFroide)
    {
        $chambreFroide->update($request->all());
        return response()->json($chambreFroide);
    }

    public function destroy(ChambreFroide $chambreFroide)
    {
        $chambreFroide->delete();
        return response()->json(['message' => 'Chambre froide supprime']);
    }
}
