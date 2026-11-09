@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
@php
    $kpis = [
        ['Lots en stock',     $lotsActifs ?? 0],
        ['Lots sortis',       $lotsSortis ?? 0],
        ['Clients',           $totalClients ?? 0],
        ['Produits',          $totalProduits ?? 0],
        ['Poids en tunnel',   number_format($poidsTunnel ?? 0, 0) . ' kg'],
        ['Poids en chambre',  number_format($poidsStock ?? 0, 0) . ' kg'],
        ['Tunnels',           $totalTunnels ?? 0],
        ['Chambres froides',  $totalChambres ?? 0],
    ];
    $statuts = [
        'en_congelation' => ['Congélation', '#b45309'],
        'en_stock'       => ['En chambre',  '#1d4ed8'],
        'sorti'          => ['Sorti',       '#15803d'],
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">Vue d'ensemble de l'activité du CDPHM</span>
    <button onclick="window.location.reload()" class="btn btn-sm btn-outline-secondary">Actualiser</button>
</div>

<div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
    @foreach($kpis as [$label, $valeur])
        <div class="col">
            <div class="card h-100" style="border-radius:4px;">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-4 fw-semibold" style="color:#1a3a5c;">{{ $valeur }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card" style="border-radius:4px;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Derniers lots enregistrés</strong>
        <a href="{{ route('activite.index') }}" class="btn btn-sm text-white" style="background:#1a3a5c;">Voir tout</a>
    </div>
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
                @forelse($derniersLots ?? [] as $lot)
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
                        <td colspan="6" class="text-center py-4 text-muted">Aucun lot enregistré pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
