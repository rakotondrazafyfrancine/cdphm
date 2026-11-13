@php
    // Bon au format du formulaire papier du CDPHM (bon d'entrée, bon de sortie) : 1/3 de feuille A4
    $dateEmission = $dateEmission ?? now();
    $duplicata = $duplicata ?? false;
    $magasinier = $emisPar ?? auth()->user()?->pseudo;
    $long = trim($__env->yieldContent('classe')) === 'long';
    $exemplaires = array_map('trim', explode('|', trim($__env->yieldContent('exemplaires'))));
    // numéros des chambres concernées (champ « N° CHAMBRE » du formulaire)
    $chambres = collect($lignes ?? [])->filter(fn ($l) => ($l['type'] ?? '') === 'Chambre')->pluck('emplacement')->unique()->implode(', ');
    $total = collect($lignes ?? [])->sum(fn ($l) => $l['poids'] ?? $l['poids_sorti'] ?? 0);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('type') - {{ $client->nom ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        :root { --noir: #111; --gris: #555; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: var(--noir); background: #e8e8e8; line-height: 1.25; }

        .barre { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; align-items: center; padding: 10px; background: #fff; border-bottom: 1px solid #ccc; }
        .barre button { padding: 8px 18px; border: 1px solid #111; border-radius: 4px; background: #111; color: #fff; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .barre button.second { background: #fff; color: #111; }
        .barre span { font-size: 11.5px; color: var(--gris); }

        .feuille { width: 210mm; margin: 12px auto; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
        .recu { position: relative; width: 210mm; height: 99mm; padding: 4mm 9mm 3mm; display: flex; flex-direction: column; overflow: hidden; border-bottom: 1px dashed #888; }
        .recu.long { height: auto; min-height: 99mm; overflow: visible; }
        .copie { position: absolute; top: 1.5mm; left: 9mm; font-size: 6.5px; letter-spacing: .1em; text-transform: uppercase; color: var(--gris); }

        /* en-tête : ministère à gauche, titre et champs à droite, comme sur le formulaire papier */
        .haut { display: flex; gap: 8mm; align-items: flex-start; }
        .ministere { width: 70mm; text-align: center; font-size: 7.3px; line-height: 1.45; text-transform: uppercase; padding-top: 3mm; }
        .ministere .tirets { letter-spacing: .1em; color: var(--gris); }
        .droite { flex: 1; }
        .rang { display: flex; align-items: baseline; justify-content: space-between; gap: 6mm; }
        .titre { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
        .duplicata { display: inline-block; margin-left: 5px; padding: .2mm 1.6mm; border: 1px solid var(--noir); font-size: 7px; letter-spacing: .1em; vertical-align: middle; }
        .champ { display: flex; align-items: baseline; gap: 1.5mm; margin-top: 1.1mm; font-size: 8.3px; }
        .champ .nom { white-space: nowrap; }
        .champ .trait { flex: 1; min-width: 10mm; border-bottom: 1px dotted var(--noir); min-height: 3.4mm; padding: 0 1mm; font-weight: 700; font-size: 8.6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .champ .moitie { flex: 1; display: flex; align-items: baseline; gap: 1.5mm; }
        .champ .moitie .trait { min-width: 5mm; }
        .sous-titre { font-size: 7px; color: var(--gris); margin-top: .8mm; }

        /* tableau du formulaire : désignation et référence / quantité / nombre de colis / colis client */
        table.grille { width: 100%; margin-top: 2.2mm; border-collapse: collapse; border: 1px solid var(--noir); font-size: 8.5px; }
        table.grille th { padding: 1mm 1.6mm; border: 1px solid var(--noir); font-size: 7.6px; font-weight: 700; text-transform: uppercase; text-align: center; line-height: 1.2; }
        table.grille th:first-child { text-align: left; width: 47%; }
        table.grille td { padding: .7mm 1.8mm; border-left: 1px solid var(--noir); border-right: 1px solid var(--noir); vertical-align: top; }
        table.grille td.q { text-align: right; white-space: nowrap; font-weight: 700; }
        table.grille td.c { text-align: center; }
        table.grille td .detail { display: block; font-size: 6.8px; color: var(--gris); }
        table.grille tr.remplissage td { height: 100%; }
        table.grille tr.total td { border-top: 1px solid var(--noir); font-weight: 700; }
        .zone { flex: 1; display: flex; flex-direction: column; }
        .zone table.grille { flex: 1; }

        .pied { display: flex; justify-content: space-between; padding: 1.2mm 6mm 0 2mm; font-size: 8.6px; }
        .pied span { min-width: 40mm; }
        .pied span.dr { text-align: right; }

        @page { size: A4 portrait; margin: 0; }
        @media print {
            body { background: #fff; }
            .barre { display: none; }
            .feuille { margin: 0; box-shadow: none; }
            .recu { page-break-inside: avoid; }
            .recu.long { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="barre">
        <button type="button" onclick="imprimer(1)">Imprimer 1 bon</button>
        @unless ($long)
            <button type="button" class="second" onclick="imprimer(3)">Imprimer 3 exemplaires (1 feuille A4)</button>
        @endunless
        <span>1 bon = 1/3 de feuille A4 (210 × 99 mm){{ $long ? ' · bon long : plus d\'un tiers de feuille' : '' }}</span>
    </div>

    <div class="feuille" id="feuille">
        <div class="recu @yield('classe')" id="recu">
            <div class="copie" id="copie"></div>

            <div class="haut">
                <div class="ministere">
                    Ministère de la Pêche et de l’Économie Bleue<br>
                    <span class="tirets">- - - - - - - - - -</span><br>
                    Secrétariat Général<br>
                    <span class="tirets">- - - - -</span><br>
                    Centre de Distribution des Produits Halieutiques de Mahajanga<br>
                    (CDPHM)
                </div>

                <div class="droite">
                    <div class="rang">
                        <div class="titre">@yield('type')@if ($duplicata) <span class="duplicata">Duplicata</span>@endif</div>
                        <div class="champ" style="margin-top:0; min-width: 40mm"><span class="nom">N° CHAMBRE :</span><span class="trait">{{ $chambres }}</span></div>
                    </div>
                    <div class="champ">
                        <span class="nom">Date :</span><span class="trait">{{ \Illuminate\Support\Carbon::parse($dateEmission)->format('d/m/Y') }}</span>
                        <span class="nom">@yield('prefixe') N° :</span><span class="trait">{{ $numero ?? '' }}</span>
                    </div>
                    <div class="champ"><span class="nom">Nom du client :</span><span class="trait">{{ $client->nom ?? '' }}</span></div>
                    <div class="champ"><span class="nom">Nom du Magasinier :</span><span class="trait">{{ $magasinier }}</span></div>
                    <div class="champ">
                        <span class="nom">Mode de livraison :</span><span class="trait"></span>
                        <span class="nom">N° Véhicule :</span><span class="trait"></span>
                    </div>
                    @if ($duplicata)<div class="sous-titre">Émis le {{ \Illuminate\Support\Carbon::parse($dateEmission)->format('d/m/Y H:i') }} · réimprimé le {{ now()->format('d/m/Y H:i') }}</div>@endif
                </div>
            </div>

            <div class="zone">
                <table class="grille">
                    <thead>
                        <tr>
                            <th>Désignation et référence</th>
                            <th>Quantité<br>Kg</th>
                            <th>Emplacement</th>
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody>
                        @yield('lignes')
                        <tr class="remplissage"><td></td><td></td><td></td><td></td></tr>
                        <tr class="total"><td>TOTAL · {{ count($lignes ?? []) }} lot{{ count($lignes ?? []) > 1 ? 's' : '' }}</td><td class="q">{{ number_format($total, 2, ',', ' ') }}</td><td></td><td></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pied"><span>Client</span><span class="dr">Magasinier</span></div>
        </div>
    </div>

    <script>
        // 3 exemplaires = les trois tiers d'une feuille A4 (client, magasinier, archives)
        const noms = @json($exemplaires);
        function imprimer(n) {
            const feuille = document.getElementById('feuille');
            const recu = document.getElementById('recu');
            const copie = document.getElementById('copie');
            const clones = [];
            if (n === 3) {
                copie.textContent = noms[0];
                for (let i = 1; i < 3; i++) {
                    const c = recu.cloneNode(true);
                    c.removeAttribute('id');
                    c.querySelector('.copie').textContent = noms[i];
                    feuille.appendChild(c);
                    clones.push(c);
                }
            } else {
                copie.textContent = '';
            }
            const nettoyer = () => { clones.forEach(c => c.remove()); copie.textContent = ''; window.removeEventListener('afterprint', nettoyer); };
            window.addEventListener('afterprint', nettoyer);
            window.print();
        }
        @if (! empty($imprimerAuto))
            window.addEventListener('load', () => imprimer(1));
        @endif
    </script>
</body>
</html>
