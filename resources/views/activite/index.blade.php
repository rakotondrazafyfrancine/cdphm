@extends('layouts.app')

@section('title', 'Activité récente')

@section('content')
@php
    $statuts = [
        'en_congelation' => ['Congélation', '#b45309'],
        'en_stock'       => ['En chambre',  '#1d4ed8'],
    ];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span class="text-muted">Tous les lots encore présents ({{ $lots->total() }})</span>
    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Retour au tableau de bord</a>
</div>

<form method="GET" action="{{ route('activite.index') }}" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="q" value="{{ $q }}" class="form-control" style="border-radius:4px;"
               placeholder="Rechercher un lot, un client, un produit...">
    </div>
    <div class="col-md-3">
        <select name="statut" class="form-select" style="border-radius:4px;">
            <option value="">Tous les statuts</option>
            <option value="en_congelation" {{ $statut === 'en_congelation' ? 'selected' : '' }}>Congélation</option>
            <option value="en_stock" {{ $statut === 'en_stock' ? 'selected' : '' }}>En chambre</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn text-white" style="background:#1a3a5c;border-radius:4px;">Filtrer</button>
        @if($q !== '' || $statut)
            <a href="{{ route('activite.index') }}" class="btn btn-outline-secondary" style="border-radius:4px;">Effacer</a>
        @endif
    </div>
</form>

<div class="card" style="border-radius:4px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-3">N° Lot</th>
                    <th>Client</th>
                    <th>Produit</th>
                    <th>Quantité (kg)</th>
                    <th>Étape</th>
                    <th>Date d'entrée</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                    @php
                        $heures = $lot->created_at ? $lot->created_at->diffInHours(now(), true) : 0;
                        $enRetard = $lot->statut === 'en_congelation' && $heures > 24;
                        [$libelle, $couleur] = $statuts[$lot->statut] ?? [$lot->statut, '#6b7280'];
                    @endphp
                    <tr class="{{ $enRetard ? 'table-warning' : '' }}">
                        <td class="ps-3 fw-semibold">{{ sprintf('LOT-%04d', $lot->id) }}</td>
                        <td>{{ $lot->client->nom ?? 'N/A' }}</td>
                        <td>{{ $lot->espece }}</td>
                        <td>{{ number_format($lot->poids_restant ?? $lot->poids_entree, 2) }}</td>
                        <td>
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $couleur }};"></span>
                            {{ $libelle }}
                            @if($enRetard)
                                <small class="text-danger">(plus de 24 h)</small>
                            @endif
                        </td>
                        <td class="text-muted">{{ $lot->created_at ? $lot->created_at->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Aucun lot trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $lots->links('pagination::bootstrap-5') }}
</div>
@endsection
