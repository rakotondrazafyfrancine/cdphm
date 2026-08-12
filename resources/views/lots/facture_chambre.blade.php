<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Chambre Froide - {{ $lot->numero_lot }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        h1 { text-align: center; color: #2d7b46; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 10px; border: 1px solid #ccc; }
        td:first-child { font-weight: bold; background: #f7f7f7; width: 220px; }
        .total-row { background: #eafaf1; font-weight: bold; font-size: 16px; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <h1>🧊 CDPHM — Facture de Sortie (Chambre Froide)</h1>

    {{-- Infos générales du lot --}}
    <table>
        <tr><td>N° Lot</td><td>{{ $lot->numero_lot }}</td></tr>
        <tr><td>Client</td><td>{{ $lot->client->nom ?? '—' }}</td></tr>
        <tr><td>Produit</td><td>{{ $lot->produit->nom ?? '—' }}</td></tr>
        <tr><td>Quantité</td><td>{{ number_format($lot->quantite, 0, ',', ' ') }} kg</td></tr>
        <tr><td>Date d'entrée (tunnel)</td><td>{{ $lot->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Date d'entrée en chambre</td><td>{{ $lot->date_entree_chambre ? \Carbon\Carbon::parse($lot->date_entree_chambre)->format('d/m/Y H:i') : '—' }}</td></tr>
        <tr><td>Date de sortie</td><td>{{ \Carbon\Carbon::parse($lot->date_sortie)->format('d/m/Y H:i') }}</td></tr>
    </table>

    {{-- Détail de la facturation --}}
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Service</th>
                <th>Détail du calcul</th>
                <th>Montant (Ar)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Congélation (Tunnel)</td>
                <td>{{ number_format($lot->quantite, 0, ',', ' ') }} kg × 450 Ar × 1 jour forfaitaire</td>
                <td>{{ number_format($lot->montant_tunnel, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Stockage (Chambre Froide)</td>
                <td>{{ number_format($lot->quantite, 0, ',', ' ') }} kg × 25 Ar × {{ $lot->jours_chambre }} jour(s)</td>
                <td>{{ number_format($lot->montant_chambre, 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">TOTAL À PAYER</td>
                <td>{{ number_format($lot->montant_tunnel + $lot->montant_chambre, 0, ',', ' ') }} Ar</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>Signature Magasinier : ___________________</div>
        <div>Signature Client : ___________________</div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
