<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bon de Sortie Tunnel - {{ $lot->numero }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            padding: 15px;
            color: #000;
            background: #fff;
            max-width: 650px;
            margin: auto;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header .titre {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0d9488;
        }

        .header .sous-titre {
            font-size: 13px;
            color: #555;
            margin-top: 3px;
        }

        .header .bon-type {
            font-size: 18px;
            font-weight: 700;
            color: #0d9488;
            border: 2px solid #0d9488;
            display: inline-block;
            padding: 4px 20px;
            margin-top: 6px;
            border-radius: 6px;
        }

        .infos-ligne {
            display: flex;
            justify-content: space-between;
            background: #f0fdfa;
            padding: 8px 14px;
            margin-bottom: 10px;
            font-size: 13px;
            border-left: 4px solid #0d9488;
            border-radius: 4px;
        }

        .infos-ligne .label {
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin: 8px 0;
        }

        td {
            padding: 7px 10px;
            border-bottom: 1px solid #ddd;
        }

        .label-col {
            font-weight: 700;
            width: 38%;
            background: #f5f7fa;
        }

        .value-col {
            width: 62%;
            font-weight: 500;
        }

        .total-box {
            background: #e8f5e9;
            border: 2px solid #2d7b46;
            padding: 10px 16px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 16px;
            border-radius: 6px;
        }

        .total-box .valeur {
            font-size: 20px;
            color: #2d7b46;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px dashed #ccc;
        }

        .signature-box {
            text-align: center;
            min-width: 100px;
        }

        .signature-box .line {
            border-top: 1.5px solid #000;
            padding-top: 6px;
            margin-top: 30px;
            font-size: 11px;
            font-weight: 500;
            color: #555;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 12px;
        }

        @media print {
            body { padding: 10px; }
            .infos-ligne, .total-box {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="titre">❄️ CDPHM - Mahajanga</div>
        <div class="sous-titre">Centre de Distribution des Produits Halieutiques</div>
        <div class="bon-type">📋 BON DE SORTIE TUNNEL</div>
    </div>

    <div class="infos-ligne">
        <span><span class="label">📅 Date sortie :</span> {{ now()->format('d/m/Y H:i') }}</span>
        <span><span class="label">📦 N° Lot :</span> {{ $lot->numero }}</span>
    </div>

    <table>
        <tr>
            <td class="label-col">👤 Client / Collecteur</td>
            <td class="value-col"><strong>{{ $lot->client->nom ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">🐟 Espèce / Produit</td>
            <td class="value-col"><strong>{{ $lot->espece ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">⚖️ Poids Entrée</td>
            <td class="value-col"><strong>{{ number_format($lot->poids_entree, 2) }} kg</strong></td>
        </tr>
        <tr>
            <td class="label-col">⏱️ Durée en tunnel</td>
            <td class="value-col">{{ round($heures, 1) }} h ({{ round($jours, 2) }} jour(s))</td>
        </tr>
        <tr>
            <td class="label-col">💰 Tarif appliqué</td>
            <td class="value-col">{{ number_format($tarif, 0, ',', ' ') }} Ar/kg/jour</td>
        </tr>
    </table>

    <div class="total-box">
        <span>💰 MONTANT À PAYER</span>
        <span class="valeur">{{ number_format($montant, 0, ',', ' ') }} Ar</span>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="line">Signature Client</div>
        </div>
        <div class="signature-box">
            <div class="line">Visa Magasinier</div>
        </div>
        <div class="signature-box">
            <div class="line">Visa Caisse</div>
        </div>
    </div>

    <div class="footer">
        Document généré automatiquement - CDPHM Mahajanga 🏭
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>

</body>
</html>
