@extends('lots.documents.bon')

@section('type', 'Bon de sortie')
@section('prefixe', 'BS')
@section('classe', count($lignes) > 6 ? 'long' : '')
@section('exemplaires', 'Exemplaire client | Exemplaire magasinier | Exemplaire caisse')

@section('lignes')
    @foreach ($lignes as $l)
        <tr>
            <td>
                {{ $l['produit'] }}
                <span class="detail">
                    {{ $l['numero'] }}
                    @if ($l['deja_sorti'] > 0) · déjà sorti avant : {{ number_format($l['deja_sorti'], 2, ',', ' ') }} kg @endif
                    @if ($l['partielle']) · reste en stock : {{ number_format($l['reste'], 2, ',', ' ') }} kg @endif
                    @if (($l['ecart'] ?? 0) != 0) · enregistré {{ number_format($l['poids_enregistre'], 2, ',', ' ') }} kg, écart {{ $l['ecart'] > 0 ? '+' : '-' }}{{ number_format(abs($l['ecart']), 2, ',', ' ') }} kg @endif
                </span>
            </td>
            <td class="q">{{ number_format($l['poids_sorti'], 2, ',', ' ') }}</td>
            <td class="c">{{ $l['type'] }} · {{ $l['emplacement'] }}</td>
            <td class="c">{{ $l['partielle'] ? 'Sortie partielle' : ($l['type'] === 'Chambre' ? 'Lot sorti' : 'Sortie totale') }}</td>
        </tr>
    @endforeach
@endsection
