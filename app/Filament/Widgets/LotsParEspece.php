<?php

namespace App\Filament\Widgets;

use App\Models\Lot;
use Filament\Widgets\ChartWidget;

class LotsParEspece extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Stock par espèce (kg)';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $parEspece = Lot::whereIn('statut', ['en_stock', 'en_congelation'])->get()
            ->groupBy(fn ($l) => $l->espece ?: 'Non précisé')
            ->map(fn ($g) => (float) $g->sum(fn ($l) => $l->poids_restant ?? $l->poids_entree))
            ->sortDesc()->take(6);

        return [
            'datasets' => [[
                'data' => $parEspece->values()->all(),
                'backgroundColor' => ['#4a90d9', '#6fc2a8', '#f0c36a', '#e58f9b', '#a794d8', '#9aa5b1'],
                'borderWidth' => 0,
            ]],
            'labels' => $parEspece->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return ['scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
