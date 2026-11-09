<?php

namespace App\Filament\Widgets;

use App\Models\ChambreFroide;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Produit;
use App\Models\Tunnel;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsLots extends StatsOverviewWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $enChambre = Lot::where('statut', 'en_stock')->get();
        $enTunnel = Lot::where('statut', 'en_congelation')->get();

        $poidsStock = $enChambre->sum(fn ($lot) => $lot->poids_restant ?? $lot->poids_entree);
        $poidsTunnel = $enTunnel->sum(fn ($lot) => $lot->poids_entree);

        return [
            Stat::make('Lots actifs', $enChambre->count() + $enTunnel->count())
                ->icon('lucide-boxes')
                ->description(Lot::where('statut', 'sorti')->count() . ' lots sortis'),
            Stat::make('Stock en chambre', number_format($poidsStock, 0, ',', ' ') . ' kg')
                ->icon('lucide-warehouse'),
            Stat::make('En congélation (tunnel)', number_format($poidsTunnel, 0, ',', ' ') . ' kg')
                ->icon('lucide-snowflake'),
            Stat::make('Clients', Client::count())
                ->icon('lucide-users'),
            Stat::make('Tunnels / Chambres', Tunnel::count() . ' / ' . ChambreFroide::count())
                ->icon('lucide-building-2'),
            Stat::make('Produits', Produit::count())
                ->icon('lucide-tag'),
        ];
    }
}
