@extends('layouts.app')

@section('title', 'Historique du client')

@section('content')
@php
    $th = 'background:#1a3a5c;color:#fff;padding:10px 16px;font-size:12px;font-weight:600;text-transform:uppercase;';
    $statutsLot = [
        'en_congelation' => ['En congélation', '#fef3c7'],
        'en_stock'       => ['En chambre', '#dbeafe'],
        'sorti'          => ['Sorti', '#dcfce7'],
    ];
    $statutsSortie = [
        'en_attente_paiement' => ['En attente de paiement', '#fef3c7'],
        'paye'                => ['Payée', '#dbeafe'],
        'sortie_effectuee'    => ['Sortie effectuée', '#dcfce7'],
    ];
    $pastille = fn($b) => 'background:' . $b . ';padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap;';
@endphp

<div style="color:#000;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">{{ $client->nom }}</h5>
        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:4px;">Retour aux clients</a>
    </div>

    <div class="card border-0 mb-3" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small>Numéro du client</small><div class="fw-semibold">{{ sprintf('CL-%03d', $client->id) }}</div></div>
                <div class="col-md-4"><small>Numéro de téléphone</small><div class="fw-semibold">{{ $client->contact ?? '—' }}</div></div>
                <div class="col-md-4"><small>Produits</small><div class="fw-semibold">{{ $client->produits->pluck('nom')->implode(', ') ?: '—' }}</div></div>
            </div>
        </div>
    </div>

    <h6 class="fw-semibold">Entrées ({{ $lots->count() }})</h6>
    <div class="card border-0 mb-4" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:#000;">
                <thead>
                    <tr>
                        <th style="{{ $th }}">N° Lot</th>
                        <th style="{{ $th }}">Produit</th>
                        <th style="{{ $th }}">Poids d'entrée</th>
                        <th style="{{ $th }}">Date d'entrée</th>
                        <th style="{{ $th }}">Date de sortie</th>
                        <th style="{{ $th }}">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lots as $lot)
                        @php [$lib, $fond] = $statutsLot[$lot->statut] ?? [$lot->statut, '#f1f5f9']; @endphp
                        <tr>
                            <td class="px-3 fw-semibold">{{ $lot->numero }}</td>
                            <td>{{ $lot->espece }}</td>
                            <td>{{ number_format($lot->poids_entree, 2) }} kg</td>
                            <td>{{ $lot->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $lot->date_sortie ? \Carbon\Carbon::parse($lot->date_sortie)->format('d/m/Y H:i') : '—' }}</td>
                            <td><span style="{{ $pastille($fond) }}">{{ $lib }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">Aucune entrée pour ce client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <h6 class="fw-semibold">Sorties ({{ $sorties->count() }})</h6>
    <div class="card border-0" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:#000;">
                <thead>
                    <tr>
                        <th style="{{ $th }}">N° Lot</th>
                        <th style="{{ $th }}">Type</th>
                        <th style="{{ $th }}">Poids sorti</th>
                        <th style="{{ $th }}">Date de la sortie</th>
                        <th style="{{ $th }}">Montant</th>
                        <th style="{{ $th }}">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sorties as $sortie)
                        @php [$lib, $fond] = $statutsSortie[$sortie->statut] ?? [$sortie->statut, '#f1f5f9']; @endphp
                        <tr>
                            <td class="px-3 fw-semibold">{{ $sortie->lot->numero ?? '—' }}</td>
                            <td>{{ $sortie->type }}</td>
                            <td>{{ number_format($sortie->poids, 2) }} kg</td>
                            <td>{{ $sortie->date->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format($sortie->montant, 0, ',', ' ') }} Ar</td>
                            <td><span style="{{ $pastille($fond) }}">{{ $lib }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">Aucune sortie pour ce client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
