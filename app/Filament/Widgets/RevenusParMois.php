<?php

namespace App\Filament\Widgets;

use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenusParMois extends ChartWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    protected ?string $heading = 'Facturation par mois (Ar)';

    protected int|string|array $columnSpan = ['md' => 2];

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $chambre = [];
        $tunnel = [];

        $debut = Carbon::now()->startOfMonth()->subMonths(11);
        $groupe = fn ($modele) => $modele::where('created_at', '>=', $debut)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mois, SUM(montant) as total")
            ->groupBy('mois')->pluck('total', 'mois');
        $parMoisChambre = $groupe(SortieChambre::class);
        $parMoisTunnel = $groupe(SortieTunnel::class);

        for ($i = 11; $i >= 0; $i--) {
            $mois = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $mois->translatedFormat('M');
            $chambre[] = (float) ($parMoisChambre[$mois->format('Y-m')] ?? 0);
            $tunnel[] = (float) ($parMoisTunnel[$mois->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [
                ['label' => 'Chambre', 'data' => $chambre, 'borderColor' => '#4a90d9', 'backgroundColor' => '#4a90d9', 'borderWidth' => 3, 'fill' => false, 'tension' => .4],
                ['label' => 'Tunnel', 'data' => $tunnel, 'borderColor' => '#8fb8e6', 'backgroundColor' => '#8fb8e6', 'borderWidth' => 3, 'fill' => false, 'tension' => .4],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
