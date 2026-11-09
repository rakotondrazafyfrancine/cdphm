@extends('lots.documents.a4')

@section('type', "Bon d'entrée")
@section('classe', count($lignes) > 8 ? 'long' : '')
@section('exemplaires', 'Exemplaire client | Exemplaire magasinier | Archives')

@section('meta')
    <tr><th>Lot{{ count($lignes) > 1 ? 's' : '' }}</th><td>{{ count($lignes) > 3 ? collect($lignes)->first()['numero'] . ' à ' . collect($lignes)->last()['numero'] : collect($lignes)->pluck('numero')->implode(' · ') }}</td></tr>
@endsection

@section('contenu')
    <table class="tableau">
        <thead>
            <tr>
                <th>N° Lot</th>
                <th>Produit</th>
                <th class="num">Poids d'entrée</th>
                <th>Emplacement</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $l)
                <tr>
                    <td class="lot">{{ $l['numero'] }}</td>
                    <td>{{ $l['produit'] }}</td>
                    <td class="num">{{ number_format($l['poids'], 2, ',', ' ') }} kg</td>
                    <td>{{ $l['type'] }} · {{ $l['emplacement'] }}</td>
                    <td>{{ $l['statut'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL · {{ count($lignes) }} produit{{ count($lignes) > 1 ? 's' : '' }}</td>
                <td class="num">{{ number_format(collect($lignes)->sum('poids'), 2, ',', ' ') }} kg</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('signatures')
    <div class="case">Le client</div>
    <div class="case">Le magasinier</div>
@endsection
