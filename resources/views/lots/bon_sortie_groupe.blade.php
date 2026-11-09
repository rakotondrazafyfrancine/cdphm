@extends('lots.documents.a4')

@section('type', 'Bon de sortie')
@section('classe', count($lignes) > 4 ? 'long' : '')
@section('exemplaires', 'Exemplaire client | Exemplaire magasinier | Exemplaire caisse')

@section('meta')
    <tr><th>Lot{{ count($lignes) > 1 ? 's' : '' }}</th><td>{{ count($lignes) > 3 ? collect($lignes)->first()['numero'] . ' à ' . collect($lignes)->last()['numero'] : collect($lignes)->pluck('numero')->implode(' · ') }}</td></tr>
@endsection

@section('contenu')
    <table class="tableau">
        <thead>
            <tr>
                <th>Lot · produit</th>
                <th>Emplacement</th>
                <th class="centre">Entrée le</th>
                <th class="num">Entré</th>
                <th class="num">Sorti</th>
                <th class="num">Reste</th>
                <th class="centre">Durée</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $l)
                <tr>
                    <td>
                        <span class="lot">{{ $l['numero'] }}</span> · {{ $l['produit'] }}
                        @if ($l['deja_sorti'] > 0)
                            <span class="detail">Déjà sorti avant : {{ number_format($l['deja_sorti'], 2, ',', ' ') }} kg</span>
                        @endif
                    </td>
                    <td>{{ $l['type'] }} · {{ $l['emplacement'] }}</td>
                    <td class="centre">{{ \Illuminate\Support\Carbon::parse($l['date_entree'])->format('d/m/Y') }}</td>
                    <td class="num">{{ number_format($l['poids_entree'], 2, ',', ' ') }} kg</td>
                    <td class="num"><strong>{{ number_format($l['poids_sorti'], 2, ',', ' ') }} kg</strong></td>
                    <td class="num">{{ $l['type'] === 'Chambre' ? number_format($l['reste'], 2, ',', ' ') . ' kg' : '—' }}</td>
                    <td class="centre">{{ $l['type'] === 'Tunnel' ? round($l['heures'], 1) . ' h' : $l['jours'] . ' j' }}</td>
                    <td class="{{ $l['partielle'] ? 'alerte' : '' }}">{{ $l['partielle'] ? 'Partielle' : ($l['type'] === 'Chambre' ? 'Lot sorti' : 'Totale') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTAL SORTI · {{ count($lignes) }} lot{{ count($lignes) > 1 ? 's' : '' }}</td>
                <td class="num">{{ number_format(collect($lignes)->sum('poids_sorti'), 2, ',', ' ') }} kg</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('signatures')
    <div class="case">Le client</div>
    <div class="case">Le magasinier</div>
    <div class="case">La caisse</div>
@endsection
