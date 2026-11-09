<?php

namespace App\Filament\Rapports\Widgets;

use App\Support\RapportFiltres;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RapportMouvementsChart extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    use InteractsWithPageFilters;

    protected ?string $heading = 'Entrées et sorties par mois (kg)';

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 2];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        [$parMoisEntrees, $parMoisSorties] = RapportFiltres::parMois($this->pageFilters);

        $debut = ! empty($this->pageFilters['date_debut'])
            ? Carbon::parse($this->pageFilters['date_debut'])->startOfMonth()
            : now()->subMonths(11)->startOfMonth();
        $fin = ! empty($this->pageFilters['date_fin'])
            ? Carbon::parse($this->pageFilters['date_fin'])->startOfMonth()
            : now()->startOfMonth();

        // pas plus de 24 mois affichés
        if ($debut->diffInMonths($fin) > 23) {
            $debut = $fin->copy()->subMonths(23);
        }

        $labels = $entrees = $sorties = [];
        for ($m = $debut->copy(); $m->lte($fin); $m->addMonth()) {
            $labels[] = $m->translatedFormat('M y');
            $entrees[] = $parMoisEntrees[$m->format('Y-m')] ?? 0.0;
            $sorties[] = $parMoisSorties[$m->format('Y-m')] ?? 0.0;
        }

        return [
            'datasets' => [
                ['label' => 'Entrées', 'data' => $entrees, 'backgroundColor' => '#3dbba4', 'borderRadius' => 6, 'borderWidth' => 0],
                ['label' => 'Sorties', 'data' => $sorties, 'backgroundColor' => '#9bdccf', 'borderRadius' => 6, 'borderWidth' => 0],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['grid' => ['display' => false]]],
        ];
    }
}
