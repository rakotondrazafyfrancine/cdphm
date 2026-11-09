<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de mouvements de stock - CDPHM</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0; font-size: 13px; color: #444; }
        .meta { font-size: 12px; color: #555; margin-bottom: 20px; }
        .resume { display: flex; gap: 20px; margin-bottom: 24px; }
        .resume div { border: 1px solid #ccc; padding: 10px 16px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 12px; }
        .signature { margin-top: 60px; border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 4px; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>CDPHM — Centre de Distribution des Produits Halieutiques de Mahajanga</h1>
        <p>Rapport de mouvements de stock</p>
    </div>

    <div class="meta">
        Période :
        @if(!empty($filtres['date_debut']) || !empty($filtres['date_fin']))
            {{ !empty($filtres['date_debut']) ? \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') : '—' }}
            au
            {{ !empty($filtres['date_fin']) ? \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') : '—' }}
        @else
            Toutes les dates
        @endif
        &nbsp;|&nbsp; Édité le {{ $dateEdition->format('d/m/Y à H:i') }}
    </div>

    <div class="resume">
        <div><strong>Entrées :</strong> {{ $indicateurs['entrees'] }} lots</div>
        <div><strong>Sorties :</strong> {{ $indicateurs['sorties'] }} lots</div>
        <div><strong>Stock actuel :</strong> {{ $indicateurs['chambre_lots'] + $indicateurs['tunnel_lots'] }} lots — {{ number_format($indicateurs['chambre_poids'] + $indicateurs['tunnel_poids'], 0, ',', ' ') }} kg</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Lot</th>
                <th>Client</th>
                <th>Produit</th>
                <th>Quantité (kg)</th>
                <th>Tunnel</th>
                <th>Chambre</th>
                <th>Statut</th>
                <th>Date entrée</th>
                <th>Date sortie</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lots as $lot)
            <tr>
                <td>{{ $lot->numero }}</td>
                <td>{{ $lot->client->nom ?? '—' }}</td>
                <td>{{ $lot->espece ?? '—' }}</td>
                <td>{{ number_format($lot->poids_entree, 2) }}</td>
                <td>{{ $lot->tunnel->nom ?? '—' }}</td>
                <td>{{ $lot->chambre->nom ?? '—' }}</td>
                <td>{{ $lot->statut === 'sorti' ? 'Sorti' : 'En stock' }}</td>
                <td>{{ ($lot->date_entree ?? $lot->date_entree_chambre ?? $lot->created_at)?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $lot->date_sortie?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div>Total : {{ $lots->count() }} lot(s)</div>
        <div class="signature">Responsable</div>
    </div>

</body>
</html>
