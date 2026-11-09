<?php

namespace App\Filament\Widgets;

use App\Models\Lot;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EntreesParMois extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Entrées de poids par mois (kg)';

    protected int|string|array $columnSpan = ['md' => 2];

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        $debut = Carbon::now()->startOfMonth()->subMonths(11);
        // une seule requête groupée par mois (au lieu d'une requête par mois)
        $parMois = Lot::where('created_at', '>=', $debut)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mois, SUM(poids_entree) as total")
            ->groupBy('mois')->pluck('total', 'mois');

        for ($i = 11; $i >= 0; $i--) {
            $mois = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $mois->translatedFormat('M');
            $values[] = (float) ($parMois[$mois->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Poids entré',
                'data' => $values,
                'backgroundColor' => '#4a90d9',
                'borderWidth' => 0,
                'borderRadius' => 8,
                'borderSkipped' => false,
            ]],
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
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['grid' => ['display' => false]]],
        ];
    }
}
