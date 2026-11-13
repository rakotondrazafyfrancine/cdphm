@php
    // Facture au format du modèle papier du CDPHM (page A4 entière)
    $dateEmission = \Illuminate\Support\Carbon::parse($dateEmission ?? now());
    $duplicata = $duplicata ?? false;
    $logo = $societeSnap['logo'] ?? \App\Models\Parametre::lire('logo');
    $nif = \App\Models\Parametre::lire('nif', '4000156744');
    $stat = \App\Models\Parametre::lire('stat', '63122412003000170');
    $email = \App\Models\Parametre::lire('email', 'cdphm@moov.mg');
    $adresse = \App\Models\Parametre::lire('adresse', 'quai barriquand -BP: 1222 MAHAJANGA');
    $tel = \App\Models\Parametre::lire('tel', '+(261) 20 62 224 79');

    $types = collect($lignes)->pluck('type')->unique();
    $prestation = $types->count() > 1 ? 'Congélation et stockage' : ($types->first() === 'Tunnel' ? 'Congélation' : 'Stockage');
    $nombre = fn ($v, $d = 2) => number_format((float) $v, floor((float) $v) == (float) $v ? 0 : $d, ',', ' ');
    $mots = ucfirst(str_replace('-', ' ', (new \NumberFormatter('fr', \NumberFormatter::SPELLOUT))->format((int) round($total))));
    $reference = collect($lignes)->pluck('numero')->implode(', ');
    $dateLongue = $dateEmission->locale('fr')->translatedFormat('j F Y');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $numero ?? '' }} - {{ $client->nom ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        :root { --noir: #111; --gris: #555; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: var(--noir); background: #e8e8e8; line-height: 1.3; }

        .barre { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; align-items: center; padding: 10px; background: #fff; border-bottom: 1px solid #ccc; }
        .barre button { padding: 8px 18px; border: 1px solid #111; border-radius: 4px; background: #111; color: #fff; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .barre span { font-size: 11.5px; color: var(--gris); }

        .page { position: relative; width: 210mm; min-height: 297mm; margin: 12px auto; padding: 14mm 14mm 12mm; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
        .duplicata { position: absolute; top: 6mm; right: 14mm; padding: .5mm 3mm; border: 1.5px solid var(--noir); font-weight: 700; letter-spacing: .14em; font-size: 11px; }

        .entete { display: grid; grid-template-columns: 1fr 66mm 30mm; gap: 6mm; align-items: start; }
        .ministere { font-size: 8px; line-height: 1.7; text-transform: uppercase; }
        .ministere div:nth-child(2) { padding-left: 8mm; }
        .ministere div:nth-child(3), .ministere div:nth-child(4) { padding-left: 4mm; }
        .ministere div:nth-child(5) { padding-left: 2mm; }
        .coord { border: 1.5px solid var(--noir); padding: 2.5mm 3mm; text-align: center; font-size: 10px; line-height: 1.6; }
        .logo-boite { position: relative; width: 28mm; height: 26mm; overflow: hidden; margin-left: auto; }
        .logo-boite img { position: absolute; left: -1.2mm; top: 0; width: 34mm; max-width: none; height: auto; }

        .filet { margin-top: 5mm; border-top: 2.5px solid var(--noir); }
        .titre-ligne { display: flex; justify-content: space-between; align-items: baseline; margin-top: 1.5mm; }
        .titre-ligne .facture { font-weight: 700; font-size: 12px; border-bottom: 1px solid var(--noir); padding-bottom: .5mm; min-width: 85mm; }
        .titre-ligne .date { font-weight: 700; font-size: 10.5px; text-decoration: underline; }

        .doit { margin-top: 9mm; text-align: center; font-weight: 700; text-decoration: underline; }
        .client { margin: 3mm 0 0 82mm; font-size: 13px; font-weight: 700; text-decoration: underline; }

        table.prestation { margin-top: 7mm; border-collapse: collapse; width: 62mm; }
        table.prestation th, table.prestation td { border: 1px solid var(--noir); text-align: center; font-weight: 700; padding: .6mm 2mm; font-size: 11px; }
        .reference { margin: 6mm 0 1mm 14mm; font-weight: 700; }

        table.facture { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        table.facture th, table.facture td { border: 1px solid var(--noir); padding: 1mm 1.6mm; }
        table.facture th { font-weight: 700; text-align: center; }
        table.facture td.c { text-align: center; }
        table.facture td.n { text-align: right; white-space: nowrap; }
        table.facture tr.total td { border-left: 0; border-right: 1px solid var(--noir); border-bottom: 0; }
        table.facture tr.total td.vide { border: 0; }
        table.facture tr.total td.cadre { border: 1px solid var(--noir); text-align: right; font-weight: 700; }
        table.facture tr.total td.lib { text-align: center; border: 0; font-weight: 700; }

        .net { display: flex; justify-content: flex-end; margin-top: 3mm; }
        .net table { border-collapse: collapse; width: 90mm; }
        .net td { border: 2px solid var(--noir); font-weight: 700; padding: .8mm 2mm; font-size: 10.5px; }
        .net td:last-child { text-align: right; }

        .arrete { margin-top: 8mm; }
        .arrete .cadre { margin-top: 1mm; border: 1.5px solid var(--noir); padding: 1mm 2mm; font-style: italic; font-weight: 700; min-height: 12mm; }
        .paiement { margin-top: 4mm; width: 110mm; border: 1px solid var(--noir); padding: .8mm 2mm; font-size: 10.5px; }
        .paiement strong { font-size: 10px; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; margin-top: 8mm; font-weight: 700; text-decoration: underline; font-size: 10.5px; }
        .signatures div:nth-child(2) { text-align: center; }
        .signatures div:nth-child(3) { text-align: center; }
        .signatures .espace { height: 42mm; }

        @page { size: A4 portrait; margin: 0; }
        @media print {
            body { background: #fff; }
            .barre { display: none; }
            .page { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="barre">
        <button type="button" onclick="window.print()">Imprimer la facture</button>
        <span>Facture au format A4 (une page)</span>
    </div>

    <div class="page">
        @if ($duplicata)<div class="duplicata">DUPLICATA</div>@endif

        <header class="entete">
            <div class="ministere">
                <div>Ministère de la Pêche et de l’Économie Bleue</div>
                <div>Secrétariat Général</div>
                <div>Direction Générale de la Pêche et de l’Économie Bleue</div>
                <div>Direction Régionale de la Pêche et de l’Économie Bleue</div>
                <div>Centre de Distribution des Produits Halieutiques de Mahajanga</div>
            </div>
            <div class="coord">
                NIF: {{ $nif }}<br>
                STAT: {{ $stat }}<br>
                Tel: {{ $tel }}<br>
                e-mail: {{ $email }}<br>
                Adresse {{ $adresse }}
            </div>
            <div class="logo-boite">
                @if ($logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="CDPHM" style="left:0; width:28mm">
                @else
                    <img src="{{ asset('images/logo-cdphm.png') }}" alt="CDPHM">
                @endif
            </div>
        </header>

        <div class="filet"></div>
        <div class="titre-ligne">
            <div class="facture">FACTURE N°{{ $numero ?? '' }}</div>
            <div class="date">Mahajanga, le {{ $dateLongue }}</div>
        </div>

        <div class="doit">DOIT</div>
        <div class="client">{{ $client->nom ?? '' }}</div>

        <table class="prestation">
            <tr><th>PRESTATION</th></tr>
            <tr><td>{{ $prestation }}</td></tr>
        </table>

        <div class="reference">Référence : {{ $reference }}</div>

        <table class="facture">
            <thead>
                <tr>
                    <th>Unité</th>
                    <th style="width: 40%">DESIGNATION</th>
                    <th>Quantité</th>
                    <th>Nb.Jours</th>
                    <th>PU HT</th>
                    <th>Montant HT</th>
                    <th>TVA</th>
                    <th>Montant TTC</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lignes as $l)
                    @php
                        $du = \Illuminate\Support\Carbon::parse($l['date_entree'])->format('d/m/y');
                        $au = \Illuminate\Support\Carbon::parse($l['date_sortie'] ?? $dateEmission)->format('d/m/y');
                        $jours = $l['type'] === 'Tunnel' ? 1 : $l['jours'];
                    @endphp
                    <tr>
                        <td class="c">Kg</td>
                        <td>
                            {{ ($l['type'] === 'Tunnel' ? 'Congélation' : 'Stockage') }} {{ $l['produit'] }} ({{ $l['numero'] }}) du {{ $du }} au {{ $au }}
                            @if ($l['partielle']) <br><em style="font-size: 9px">sortie partielle, reste {{ $nombre($l['reste']) }} kg</em> @endif
                        </td>
                        <td class="n">{{ $nombre($l['poids_facture'] ?? $l['poids_sorti']) }}</td>
                        <td class="c">{{ $jours }}</td>
                        <td class="n">{{ $nombre($l['tarif']) }}</td>
                        <td class="n">{{ number_format($l['montant'], 2, ',', ' ') }}</td>
                        <td class="n">0,00</td>
                        <td class="n">{{ number_format($l['montant'], 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td class="vide"></td><td class="lib">Total</td><td class="vide"></td><td class="vide"></td><td class="vide"></td><td class="vide"></td><td class="vide"></td>
                    <td class="cadre">{{ number_format($total, 2, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="net">
            <table>
                <tr><td>NET A PAYER</td><td>ARIARY</td><td>{{ number_format($total, 2, ',', ' ') }}</td></tr>
            </table>
        </div>

        <div class="arrete">
            Arrêtée la présente facture à la somme de :
            <div class="cadre">*** {{ $mots }} ariary ***</div>
        </div>

        <div class="paiement"><strong>REFERENCE DE PAIEMENT :</strong>
            {{ ($statut ?? '') === 'PAYEE' ? 'Payée' : (($statut ?? '') === 'EN ATTENTE DE PAIEMENT' ? 'En attente de paiement' : 'Paiement partiel') }}
            · {{ $numero ?? '' }}
        </div>

        <div class="signatures">
            <div>Le Client</div>
            <div>Facturière</div>
            <div>Le Chef de Département<br>Administratif et Financier</div>
        </div>
        <div class="signatures"><div class="espace"></div></div>
    </div>
</body>
</html>
