@extends('layouts.app')

@section('title', 'Clients')

@section('content')
@php
    $btn = 'display:inline-block;box-sizing:border-box;min-width:84px;height:32px;line-height:30px;padding:0 12px;border:1px solid #cbd5e1;border-radius:4px;color:#000;font-size:13px;font-weight:600;text-align:center;text-decoration:none;cursor:pointer;background:#f1f5f9;';
    $th  = 'background:#1a3a5c;color:#fff;padding:10px 16px;font-size:12px;font-weight:600;text-transform:uppercase;';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3" style="color:#000;">
    <div>
        <h5 class="fw-semibold mb-0">Liste des clients</h5>
        <small>{{ $clients->count() }} client(s) enregistré(s)</small>
    </div>
    <a href="{{ route('clients.create') }}" class="btn btn-sm text-white px-3" style="background:#1a3a5c;border-radius:4px;">+ Nouveau client</a>
</div>

<div class="card border-0" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:#000;">
            <thead>
                <tr>
                    <th style="{{ $th }}">N°</th>
                    <th style="{{ $th }}">Nom / Coopérative</th>
                    <th style="{{ $th }}">Numéro</th>
                    <th style="{{ $th }}">Produits</th>
                    <th style="{{ $th }}">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td class="px-3 fw-semibold" style="vertical-align:middle;">{{ sprintf('CL-%03d', $client->id) }}</td>
                        <td style="vertical-align:middle;">{{ $client->nom }}</td>
                        <td style="vertical-align:middle;">{{ $client->contact ?? '—' }}</td>
                        <td style="vertical-align:middle;">{{ $client->produits->pluck('nom')->implode(', ') ?: '—' }}</td>
                        <td style="vertical-align:middle;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                {{-- <a href="{{ route('clients.show', $client->id) }}" style="{{ $btn }}">Voir</a> --}}
                                <a href="{{ route('clients.edit', $client->id) }}" style="{{ $btn }}">Modifier</a>
                                <form method="POST" action="{{ route('clients.destroy', $client->id) }}" style="margin:0;" onsubmit="return confirm('Supprimer ce client ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="{{ $btn }}">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Aucun client enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
