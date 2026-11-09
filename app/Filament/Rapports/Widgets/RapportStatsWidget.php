<?php

namespace App\Filament\Rapports\Widgets;

use App\Support\RapportFiltres;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RapportStatsWidget extends StatsOverviewWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $i = RapportFiltres::totaux($this->pageFilters);
        $kg = fn ($v) => number_format($v, 0, ',', ' ') . ' kg';

        return [
            Stat::make('Entrées', (int) $i['entrees'])
                ->description($kg($i['entrees_poids']) . ' reçus')
                ->icon('lucide-package-plus')->color('gray')->extraAttributes(['class' => 'cd-stat-rapport']),
            Stat::make('Sorties', (int) $i['sorties'])
                ->description($kg($i['sorties_poids']) . ' sortis')
                ->icon('lucide-package-open')->color('gray')->extraAttributes(['class' => 'cd-stat-rapport']),
            Stat::make('En tunnel', (int) $i['tunnel_lots'] . ' lots')
                ->description($kg($i['tunnel_poids']))
                ->icon('lucide-snowflake')->color('gray')->extraAttributes(['class' => 'cd-stat-rapport']),
            Stat::make('En chambre', (int) $i['chambre_lots'] . ' lots')
                ->description($kg($i['chambre_poids']))
                ->icon('lucide-thermometer-snowflake')->color('gray')->extraAttributes(['class' => 'cd-stat-rapport']),
        ];
    }
}
