@extends('lots.documents.a4')

@section('type', 'Facture')
@section('classe', count($lignes) > 4 ? 'long' : '')
@section('exemplaires', 'Exemplaire client | Exemplaire caisse | Archives')

@section('meta')
    <tr><th>N° facture</th><td>{{ collect($lignes)->map(fn ($l) => ($l['type'] === 'Tunnel' ? 'T' : 'C') . $l['id'])->implode(' · ') }}</td></tr>
@endsection

@section('contenu')
    <table class="tableau">
        <thead>
            <tr>
                <th>Lot · produit</th>
                <th>Emplacement</th>
                <th class="centre">Entrée le</th>
                <th class="num">Poids facturé</th>
                <th class="centre">Durée</th>
                <th class="num">Tarif (Ar/kg)</th>
                <th class="num">Montant (Ar)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $l)
                <tr>
                    <td>
                        <span class="lot">{{ $l['numero'] }}</span> · {{ $l['produit'] }}
                        <span class="detail">Entré {{ number_format($l['poids_entree'], 2, ',', ' ') }} kg
                            @if ($l['deja_sorti'] > 0) · déjà sorti {{ number_format($l['deja_sorti'], 2, ',', ' ') }} kg @endif
                            @if ($l['partielle']) · <span class="alerte">sortie partielle, reste {{ number_format($l['reste'], 2, ',', ' ') }} kg</span> @endif
                        </span>
                    </td>
                    <td>{{ $l['type'] }} · {{ $l['emplacement'] }}</td>
                    <td class="centre">{{ \Illuminate\Support\Carbon::parse($l['date_entree'])->format('d/m/Y') }}</td>
                    <td class="num">{{ number_format($l['poids_sorti'], 2, ',', ' ') }} kg</td>
                    <td class="centre">{{ $l['type'] === 'Tunnel' ? '1 jour' : $l['jours'] . ' jour' . ($l['jours'] > 1 ? 's' : '') }}</td>
                    <td class="num">{{ number_format($l['tarif'], 0, ',', ' ') }}{{ $l['type'] === 'Chambre' ? ' /jour' : '' }}</td>
                    <td class="num"><strong>{{ number_format($l['montant'], 0, ',', ' ') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('signatures')
    <div class="case">Le client</div>
    <div class="case">La caisse</div>
@endsection

@section('total')
    <div style="text-align: right;">
        <div class="total-boite">
            <div class="ligne"><span>{{ count($lignes) }} lot{{ count($lignes) > 1 ? 's' : '' }} · {{ number_format(collect($lignes)->sum('poids_sorti'), 2, ',', ' ') }} kg</span>
                <span class="etat {{ $statut === 'PAYEE' ? 'paye' : ($statut === 'EN ATTENTE DE PAIEMENT' ? 'attente' : 'partiel') }}">{{ $statut === 'PAYEE' ? 'PAYÉE' : $statut }}</span></div>
            <div class="ligne grand"><span>TOTAL À PAYER</span><span>{{ number_format($total, 0, ',', ' ') }} Ar</span></div>
        </div>
    </div>
@endsection
