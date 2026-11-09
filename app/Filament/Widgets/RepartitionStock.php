<?php

namespace App\Filament\Widgets;

use App\Models\Lot;
use Filament\Widgets\ChartWidget;

class RepartitionStock extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Répartition du stock';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $chambre = Lot::where('statut', 'en_stock')->get()
            ->sum(fn ($lot) => $lot->poids_restant ?? $lot->poids_entree);
        $tunnel = Lot::where('statut', 'en_congelation')->sum('poids_entree');

        return [
            'datasets' => [[
                'data' => [(float) $chambre, (float) $tunnel],
                'backgroundColor' => ['#8fb8e6', '#4a90d9'],
                'borderWidth' => 0,
            ]],
            'labels' => ['Chambres froides', 'Tunnels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return ['cutout' => '72%', 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
