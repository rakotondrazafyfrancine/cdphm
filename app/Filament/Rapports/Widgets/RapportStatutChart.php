<?php

namespace App\Filament\Rapports\Widgets;

use App\Support\RapportFiltres;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RapportStatutChart extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    use InteractsWithPageFilters;

    protected ?string $heading = 'Répartition des lots';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $t = RapportFiltres::totaux($this->pageFilters);

        return [
            'datasets' => [[
                'data' => [
                    (int) $t['tunnel_lots'],
                    (int) $t['chambre_lots'],
                    (int) $t['sorties'],
                ],
                'backgroundColor' => ['#3dbba4', '#9bdccf', '#8896a6'],
                'borderWidth' => 0,
            ]],
            'labels' => ['En tunnel', 'En chambre', 'Sortis'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
