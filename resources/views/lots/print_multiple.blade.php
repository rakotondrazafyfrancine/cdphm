@extends('lots.documents.bon')

@section('type', "Bon d'entrée")
@section('prefixe', 'BE')
@section('classe', count($lignes) > 6 ? 'long' : '')
@section('exemplaires', 'Exemplaire client | Exemplaire magasinier | Archives')

@section('lignes')
    @foreach ($lignes as $l)
        <tr>
            <td>{{ $l['produit'] }} <span class="detail">{{ $l['numero'] }}</span></td>
            <td class="q">{{ number_format($l['poids'], 2, ',', ' ') }}</td>
            <td class="c">{{ $l['type'] }} · {{ $l['emplacement'] }}</td>
            <td class="c">{{ $l['statut'] }}</td>
        </tr>
    @endforeach
@endsection
