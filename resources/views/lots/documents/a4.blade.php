@php
    // société telle qu'elle était à l'émission (document archivé), sinon valeurs actuelles
    $societe = $societeSnap['nom'] ?? \App\Models\Parametre::lire('nom', 'CDPHM');
    $logo = $societeSnap['logo'] ?? \App\Models\Parametre::lire('logo');
    $societeContact = $societeSnap['contact'] ?? \App\Models\Parametre::lire('contact');
    $societeLieu = $societeSnap['lieu'] ?? \App\Models\Parametre::lire('lieu');
    $dateEmission = $dateEmission ?? now();
    $duplicata = $duplicata ?? false;
    $long = trim($__env->yieldContent('classe')) === 'long';
    $exemplaires = trim($__env->yieldContent('exemplaires')) !== ''
        ? array_map('trim', explode('|', trim($__env->yieldContent('exemplaires'))))
        : ['Exemplaire client', 'Exemplaire magasinier', 'Exemplaire caisse'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('type') - {{ $client->nom ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        :root { --noir: #111; --gris: #555; --trait: #9a9a9a; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5px; color: var(--noir); background: #e8e8e8; line-height: 1.3; }

        /* barre d'outils (jamais imprimée) */
        .barre { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; align-items: center; padding: 10px; background: #fff; border-bottom: 1px solid #ccc; }
        .barre button { padding: 8px 18px; border: 1px solid #111; border-radius: 4px; background: #111; color: #fff; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .barre button.second { background: #fff; color: #111; }
        .barre span { font-size: 11.5px; color: var(--gris); }

        /* la feuille A4 est coupée en trois : un reçu occupe un tiers (210 x 99 mm) */
        .feuille { width: 210mm; margin: 12px auto; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
        .recu { position: relative; width: 210mm; height: 99mm; padding: 5mm 10mm 4mm; display: flex; flex-direction: column; overflow: hidden; border-bottom: 1px dashed #888; }
        .recu.long { height: auto; min-height: 99mm; overflow: visible; }
        .copie { position: absolute; top: 2mm; right: 10mm; font-size: 7px; letter-spacing: .1em; text-transform: uppercase; color: var(--gris); }

        .entete { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .societe { display: flex; align-items: center; gap: 8px; }
        .logo { height: 11mm; width: auto; object-fit: contain; }
        .societe .nom { font-size: 15px; font-weight: 700; letter-spacing: .05em; line-height: 1.1; }
        .societe .sous { font-size: 7px; color: var(--gris); margin-top: 1px; }
        .societe .coord { font-size: 7.5px; margin-top: 1px; }
        .document { text-align: right; }
        .document .type { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; line-height: 1.1; }
        table.meta { margin-left: auto; margin-top: 2px; border-collapse: collapse; font-size: 8px; }
        table.meta th { text-align: right; font-weight: 400; color: var(--gris); padding: 0 5px 0 0; }
        table.meta td { text-align: right; font-weight: 700; padding: 0; }

        .duplicata { display: inline-block; vertical-align: middle; margin-left: 6px; padding: .2mm 2mm; border: 1px solid var(--noir); font-size: 7.5px; letter-spacing: .1em; }
        .accent { height: 0; margin: 2.5mm 0; border-top: 1.5px solid var(--noir); }

        .client { display: flex; align-items: baseline; gap: 6px; margin-bottom: 2.5mm; }
        .client span { font-size: 8px; text-transform: uppercase; color: var(--gris); }
        .client strong { font-size: 11px; }

        table.tableau { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        table.tableau thead th { text-align: left; padding: .9mm 1.6mm; font-size: 7.5px; font-weight: 700; text-transform: uppercase; border-top: 1px solid var(--noir); border-bottom: 1px solid var(--noir); }
        table.tableau td { padding: .9mm 1.6mm; border-bottom: 1px solid #d4d4d4; vertical-align: top; }
        table.tableau .num { text-align: right; white-space: nowrap; }
        table.tableau .centre { text-align: center; }
        table.tableau .lot { font-weight: 700; }
        table.tableau .detail { display: block; font-size: 7px; color: var(--gris); }
        table.tableau .alerte { font-weight: 700; }
        table.tableau tfoot td { padding: 1.1mm 1.6mm; font-weight: 700; border-top: 1px solid var(--noir); border-bottom: 0; }

        .bas { margin-top: auto; display: flex; align-items: flex-end; justify-content: space-between; gap: 8mm; padding-top: 2mm; }
        .signatures { display: flex; gap: 4mm; flex: 1; }
        .signatures .case { flex: 1; height: 12mm; border: 1px solid var(--trait); padding: 1mm 2mm; font-size: 7px; color: var(--gris); text-transform: uppercase; letter-spacing: .04em; }
        .total-boite { min-width: 58mm; border: 1px solid var(--noir); }
        .total-boite .ligne { display: flex; justify-content: space-between; align-items: center; padding: .5mm 2.5mm; font-size: 8px; }
        .total-boite .ligne.grand { border-top: 1px solid var(--noir); font-size: 11px; font-weight: 700; padding: 1mm 2.5mm; }
        .etat { display: inline-block; padding: .2mm 2mm; border: 1px solid var(--noir); font-weight: 700; letter-spacing: .06em; font-size: 7.5px; }

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
        <button type="button" onclick="imprimer(1)">Imprimer 1 reçu</button>
        @unless ($long)
            <button type="button" class="second" onclick="imprimer(3)">Imprimer 3 exemplaires (1 feuille A4)</button>
        @endunless
        <span>1 reçu = 1/3 de feuille A4 (210 × 99 mm){{ $long ? ' · reçu long : plus d\'un tiers de feuille' : '' }}</span>
    </div>

    <div class="feuille" id="feuille">
        <div class="recu @yield('classe')" id="recu">
            <div class="copie" id="copie"></div>

            <header class="entete">
                <div class="societe">
                    @if ($logo)
                        <img class="logo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ $societe }}">
                    @endif
                    <div>
                        <div class="nom">{{ $societe }}</div>
                        @if (strcasecmp($societe, 'CDPHM') === 0)
                            <div class="sous">Centre de Distribution des Produits Halieutiques de Mahajanga</div>
                        @endif
                        @if (filled($societeLieu) || filled($societeContact))
                            <div class="coord">
                                {{ $societeLieu }}@if (filled($societeLieu) && filled($societeContact)) · @endif
                                @if (filled($societeContact))Tél : {{ $societeContact }}@endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="document">
                    <div class="type">@yield('type')@if ($duplicata) <span class="duplicata">Duplicata</span>@endif</div>
                    <table class="meta">
                        @isset($numero)<tr><th>N°</th><td>{{ $numero }}</td></tr>@endisset
                        <tr><th>{{ $duplicata ? 'Émis le' : 'Date' }}</th><td>{{ \Illuminate\Support\Carbon::parse($dateEmission)->format('d/m/Y H:i') }}</td></tr>
                        @if ($duplicata)<tr><th>Réimprimé le</th><td>{{ now()->format('d/m/Y H:i') }}</td></tr>@endif
                        @yield('meta')
                    </table>
                </div>
            </header>

            <div class="accent"></div>

            <section class="client"><span>Client</span><strong>{{ $client->nom ?? '—' }}</strong></section>

            @yield('contenu')

            <div class="bas">
                <div class="signatures">@yield('signatures')</div>
                @yield('total')
            </div>
        </div>
    </div>

    <script>
        // 3 exemplaires = les trois tiers d'une feuille A4 (client, magasinier, caisse)
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
