<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon d'entrée - {{ $lot->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .print-container {
            max-width: 720px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 30px 35px;
            border-top: 5px solid #1a3a5c;
            position: relative;
        }
        /* En-tête */
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #1a3a5c;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 2px;
        }
        .header .badge {
            display: inline-block;
            background: #0d9488;
            color: white;
            padding: 4px 20px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 600;
            margin-top: 6px;
        }
        .header .date {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Infos lot (statut) */
        .lot-info {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 16px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }
        .lot-info span { font-size: 14px; }
        .lot-info .statut {
            background: #0d9488;
            color: white;
            padding: 2px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        /* Grille 2 colonnes */
        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 30px;
            margin: 12px 0;
        }
        .grid-2col .item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        .grid-2col .item .label {
            font-weight: 600;
            color: #1a3a5c;
            font-size: 13px;
        }
        .grid-2col .item .value {
            font-weight: 500;
            color: #0f172a;
            font-size: 13px;
        }

        /* Infrastructure */
        .infra-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 14px 0;
            border-left: 4px solid #0d9488;
        }
        .infra-box .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        .infra-box .row .label {
            font-weight: 600;
            color: #1a3a5c;
        }

        /* Contre-pesée */
        .contre-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 10px;
            padding: 12px 18px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .contre-box .label { font-weight: 600; color: #92400e; font-size: 14px; }
        .contre-box .value { font-weight: 700; color: #92400e; font-size: 16px; }

        /* Montant */
        .montant-box {
            background: #ecfdf5;
            border-left: 5px solid #0d9488;
            padding: 14px 20px;
            border-radius: 10px;
            margin: 16px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .montant-box .label { font-weight: 600; font-size: 16px; color: #1a3a5c; }
        .montant-box .valeur { font-size: 22px; font-weight: 700; color: #0d9488; }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-around;
            margin-top: 28px;
            gap: 15px;
        }
        .signature-box {
            text-align: center;
            width: 150px;
        }
        .signature-box .line {
            border-top: 2px solid #1a3a5c;
            margin-top: 36px;
            padding-top: 8px;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 20px;
            font-size: 11px;
            color: #94a3b8;
        }
        .footer .mention { font-weight: 500; color: #1a3a5c; }

        /* Bouton impression */
        .btn-print {
            background: #1a3a5c;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
        }
        .btn-print:hover { background: #0f2a44; }
        .no-print { text-align: center; margin-top: 12px; }

        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .print-container { box-shadow: none; border-top: none; padding: 20px; max-width: 100%; border-radius: 0; }
            .statut { background: #0d9488 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .montant-box { background: #ecfdf5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .contre-box { background: #fffbeb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .infra-box { background: #f1f5f9 !important; }
            .lot-info { background: #f8fafc !important; }
        }
    </style>
</head>
<body>
<div class="print-container" id="print-area">

    <!-- ========== EN-TÊTE ========== -->
    <div class="header">
        <h1>🌊 CDPHM - Mahajanga</h1>
        <div class="subtitle">Centre de Distribution des Produits Halieutiques</div>
        <div class="badge">✅ BON D'ENTRÉE</div>
        <div class="date">📅 {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- ========== INFOS LOT ========== -->
    <div class="lot-info">
        <span><strong>📋 N° Lot :</strong> {{ $lot->numero }}</span>
        <span><strong>📌 Statut :</strong> <span class="statut">@if ($type === 'tunnel') En congelation @else En stock
            @endif</span></span>
    </div>

    <!-- ========== GRILLE ========== -->
    <div class="grid-2col">
        <div class="item"><span class="label">👤 Client</span><span class="value">{{ $lot->client->nom ?? '—' }}</span></div>
        <div class="item"><span class="label">📞 Contact</span><span class="value">{{ $lot->client->contact ?? '—' }}</span></div>
        <div class="item"><span class="label">🐟 Espèce</span><span class="value">{{ $lot->espece ?? 'N/A' }}</span></div>

        @if($type === 'tunnel')
            <div class="item"><span class="label">📍 Origine</span><span class="value">frais, à congeler</span></div>
        @endif

        <div class="item"><span class="label">⚖️ Poids entrée</span><span class="value">{{ number_format($lot->poids_entree, 2) }} kg</span></div>

        @if($type === 'tunnel')
            <div class="item"><span class="label">📦 Décompte</span><span class="value">F:{{ $lot->nb_filets ?? 0 }} / P:{{ $lot->nb_poissons ?? 0 }} / B:{{ $lot->nb_bacs ?? 0 }}</span></div>
        @endif
    </div>

    <!-- ========== INFRASTRUCTURE ========== -->
    <div class="infra-box">
        <div class="row"><span class="label">{{ $type === 'tunnel' ? '🌡️ Tunnel' : '🧊 Chambre' }}</span><span>{{ $infrastructure->nom ?? 'Non assigné' }}</span></div>
        <div class="row"><span class="label">⏱️ {{ $dureeLabel }}</span><span>{{ $duree }} jour(s)</span></div>
        <div class="row"><span class="label">💰 Tarif appliqué</span><span>{{ $tarif }} Ar/kg/jour</span></div>
        <div class="row"><span class="label">👷 Manutention</span><span>{{ $lot->dockers ?? '—' }}</span></div>
    </div>

    <!-- ========== CONTRE-PESÉE (chambre uniquement) ========== -->
    @if($type === 'chambre')
        <div class="contre-box">
            <span class="label">⚖️ Contre-pesée (poids sortie)</span>
            <span class="value">{{ number_format($lot->poids_sortie ?? $lot->poids_entree, 2) }} kg</span>
        </div>
    @endif

    <!-- ========== MONTANT ESTIMÉ ========== -->
    <div class="montant-box">
        <span class="label">💰 MONTANT ESTIMÉ</span>
        <span class="valeur">{{ number_format($montantEstime, 0, ',', ' ') }} Ar</span>
    </div>

    <!-- ========== SIGNATURES ========== -->
    <div class="signatures">
        <div class="signature-box"><div class="line">Signature Client</div></div>
        <div class="signature-box"><div class="line">Visa Magasinier</div></div>
        <div class="signature-box"><div class="line">Visa Responsable</div></div>
    </div>

    <!-- ========== FOOTER ========== -->
    <div class="footer">
        <span class="mention">CDPHM</span> – Mahajanga – Tous droits réservés
    </div>

    <!-- ========== BOUTON IMPRESSION ========== -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimer</button>
    </div>

</div>
</body>
</html>
