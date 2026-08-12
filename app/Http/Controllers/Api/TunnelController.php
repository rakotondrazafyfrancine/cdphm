<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tunnel;
use Illuminate\Http\Request;

class TunnelController extends Controller
{
    public function index()
    {
        return response()->json(Tunnel::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'capacite_tonnes' => 'required|numeric',
        ]);

        return response()->json(Tunnel::create($request->all()), 201);
    }

    public function show(Tunnel $tunnel)
    {
        return response()->json($tunnel);
    }

    public function update(Request $request, Tunnel $tunnel)
    {
        $tunnel->update($request->all());
        return response()->json($tunnel);
    }

    public function destroy(Tunnel $tunnel)
    {
        $tunnel->delete();
        return response()->json(['message' => 'Tunnel supprime']);
    }
}
