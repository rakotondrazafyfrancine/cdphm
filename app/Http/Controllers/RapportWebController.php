<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use App\Models\Tunnel;
use App\Models\ChambreFroide;
use App\Models\SortieChambre;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class RapportWebController extends Controller
{
    private function lotsFiltres(Request $request)
    {
        $query = Lot::with(['client', 'tunnel', 'chambre']);

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('espece')) {
            $query->where('espece', $request->espece);
        }

        if ($request->filled('tunnel_id')) {
            $query->where('tunnel_id', $request->tunnel_id);
        }

        if ($request->filled('chambre_id')) {
            $query->where('chambre_id', $request->chambre_id);
        }

        if ($request->filled('mouvement')) {
            if ($request->mouvement === 'sortie') {
                $query->where('statut', 'sorti');
            } elseif ($request->mouvement === 'entree') {
                $query->where('statut', 'en_stock');
            }
        }

        return $query->orderByDesc('created_at');
    }

    private function calculerIndicateurs($lots)
{
    $enChambre = $lots->where('statut', 'en_stock');
    $enTunnel  = $lots->where('statut', 'en_congelation');
    $sortis    = $lots->where('statut', 'sorti');

    return [
        'entrees'        => $lots->count(),
        'sorties'        => $sortis->count(),
        'chambre_lots'   => $enChambre->count(),
        'chambre_poids'  => $enChambre->sum(fn($lot) => $lot->poids_restant ?? $lot->poids_entree),
        'tunnel_lots'    => $enTunnel->count(),
        'tunnel_poids'   => $enTunnel->sum('poids_entree'),
    ];
}

    public function index(Request $request)
    {
        $lots = $this->lotsFiltres($request)->with('sortiesChambre')->get();
        $indicateurs = $this->calculerIndicateurs($lots);

        $sortiesPartielles = SortieChambre::with('lot.client')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('rapports.index', [
            'lots'              => $lots,
            'indicateurs'       => $indicateurs,
            'clients'           => Client::orderBy('nom')->get(),
            'tunnels'           => Tunnel::orderBy('nom')->get(),
            'chambres'          => ChambreFroide::orderBy('nom')->get(),
            'especes'           => Lot::whereNotNull('espece')->distinct()->pluck('espece'),
            'sortiesPartielles' => $sortiesPartielles,
            'filtres'           => $request->all(),
        ]);
    }

    public function imprimer(Request $request)
    {
        $lots = $this->lotsFiltres($request)->get();
        $indicateurs = $this->calculerIndicateurs($lots);

        return view('rapports.imprimer', [
            'lots'        => $lots,
            'indicateurs' => $indicateurs,
            'filtres'     => $request->all(),
            'dateEdition' => now(),
        ]);
    }

    /** Export Excel (.xlsx) du rapport, avec les mêmes filtres que la page. */
    public function exporterExcel(Request $request)
    {
        $lots = $this->lotsFiltres($request)->get();
        $indicateurs = $this->calculerIndicateurs($lots);

        $chemin = tempnam(sys_get_temp_dir(), 'rapport_') . '.xlsx';

        $options = new Options();
        $options->setColumnWidth(12, 1);   // N° Lot
        $options->setColumnWidth(28, 2);   // Client
        $options->setColumnWidth(24, 3);   // Produit
        $options->setColumnWidth(16, 4);   // Poids entré
        $options->setColumnWidth(16, 5);   // Poids restant
        $options->setColumnWidth(16, 6);   // Tunnel
        $options->setColumnWidth(16, 7);   // Chambre
        $options->setColumnWidth(16, 8);   // Statut
        $options->setColumnWidth(14, 9);   // Date entrée
        $options->setColumnWidth(14, 10);  // Date sortie

        $writer = new Writer($options);
        $writer->openToFile($chemin);

        $entete = (new Style())->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor('0B3A66')
            ->setCellAlignment(CellAlignment::CENTER)->setShouldWrapText();
        $texte = new Style();
        $poids = (new Style())->setFormat('#,##0.00');
        $date = (new Style())->setFormat('dd/mm/yyyy')->setCellAlignment(CellAlignment::CENTER);
        $total = (new Style())->setFontBold()->setBackgroundColor('E3F3FC');
        $totalPoids = (new Style())->setFontBold()->setBackgroundColor('E3F3FC')->setFormat('#,##0.00');
        $titre = (new Style())->setFontBold()->setFontSize(14)->setFontColor('0B3A66');
        $gras = (new Style())->setFontBold();

        $cell = fn ($valeur, ?Style $style = null) => Cell::fromValue($valeur, $style);
        $statuts = ['en_congelation' => 'En congélation', 'en_stock' => 'En stock', 'sorti' => 'Sorti'];

        // ---- feuille 1 : mouvements
        $writer->getCurrentSheet()->setName('Mouvements');
        $writer->addRow(new Row(array_map(fn ($t) => $cell($t, $entete), [
            'N° Lot', 'Client', 'Produit', 'Poids entré (kg)', 'Poids restant (kg)', 'Tunnel', 'Chambre', 'Statut', 'Date entrée', 'Date sortie',
        ])));

        $totalEntre = 0.0;
        $totalRestant = 0.0;
        foreach ($lots as $lot) {
            $restant = $lot->statut === 'sorti' ? 0.0 : (float) ($lot->poids_restant ?? $lot->poids_entree);
            $totalEntre += (float) $lot->poids_entree;
            $totalRestant += $restant;

            $entree = $lot->date_entree_chambre ?? $lot->created_at;

            $writer->addRow(new Row([
                $cell($lot->numero, $texte),
                $cell($lot->client->nom ?? '', $texte),
                $cell($lot->espece ?? '', $texte),
                $cell((float) $lot->poids_entree, $poids),
                $cell($restant, $poids),
                $cell($lot->tunnel->nom ?? '', $texte),
                $cell($lot->chambre->nom ?? '', $texte),
                $cell($statuts[$lot->statut] ?? (string) $lot->statut, $texte),
                $cell($entree ? $entree->toDateTimeImmutable() : '', $date),
                $cell($lot->date_sortie ? $lot->date_sortie->toDateTimeImmutable() : '', $date),
            ]));
        }

        $writer->addRow(new Row([
            $cell('TOTAL', $total), $cell($lots->count() . ' lot(s)', $total), $cell('', $total),
            $cell($totalEntre, $totalPoids), $cell($totalRestant, $totalPoids),
            $cell('', $total), $cell('', $total), $cell('', $total), $cell('', $total), $cell('', $total),
        ]));

        // ---- feuille 2 : résumé
        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Résumé');
        $writer->addRow(new Row([$cell('Rapport des entrées, sorties et stock', $titre)]));
        $writer->addRow(new Row([$cell('Édité le', $gras), $cell(now()->format('d/m/Y H:i'), $texte)]));
        $writer->addRow(new Row([]));
        $writer->addRow(new Row([$cell('Indicateur', $entete), $cell('Lots', $entete), $cell('Poids (kg)', $entete)]));
        foreach ([
            ['Entrées (lots du rapport)', $indicateurs['entrees'], $totalEntre],
            ['Sorties', $indicateurs['sorties'], $lots->where('statut', 'sorti')->sum('poids_entree')],
            ['En tunnel', $indicateurs['tunnel_lots'], $indicateurs['tunnel_poids']],
            ['En chambre', $indicateurs['chambre_lots'], $indicateurs['chambre_poids']],
        ] as [$libelle, $nombre, $kg]) {
            $writer->addRow(new Row([$cell($libelle, $texte), $cell((int) $nombre, $texte), $cell((float) $kg, $poids)]));
        }

        $filtres = array_filter($request->only(['date_debut', 'date_fin', 'mouvement', 'espece', 'client_id', 'tunnel_id', 'chambre_id']), fn ($v) => filled($v));
        if ($filtres) {
            $writer->addRow(new Row([]));
            $writer->addRow(new Row([$cell('Filtres appliqués', $gras)]));
            $noms = [
                'date_debut' => 'Entrée à partir du', 'date_fin' => 'Entrée jusqu\'au', 'mouvement' => 'Mouvement',
                'espece' => 'Produit', 'client_id' => 'Client', 'tunnel_id' => 'Tunnel', 'chambre_id' => 'Chambre',
            ];
            foreach ($filtres as $cle => $valeur) {
                $affiche = match ($cle) {
                    'client_id' => Client::find($valeur)?->nom ?? $valeur,
                    'tunnel_id' => Tunnel::find($valeur)?->nom ?? $valeur,
                    'chambre_id' => ChambreFroide::find($valeur)?->nom ?? $valeur,
                    'mouvement' => $valeur === 'sortie' ? 'Sorties' : 'En stock',
                    default => $valeur,
                };
                $writer->addRow(new Row([$cell($noms[$cle], $texte), $cell((string) $affiche, $texte)]));
            }
        }

        $writer->close();

        return response()->download(
            $chemin,
            'rapport_stock_' . now()->format('Y-m-d_His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
