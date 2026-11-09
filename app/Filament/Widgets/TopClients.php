<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopClients extends TableWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Meilleurs clients (poids entré)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()->withCount('lots')->withSum('lots', 'poids_entree')
                    ->orderByDesc('lots_sum_poids_entree')->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('nom')->label('Client'),
                TextColumn::make('type')->badge(),
                TextColumn::make('lots_count')->label('Lots'),
                TextColumn::make('lots_sum_poids_entree')->label('Poids total (kg)')->numeric(2),
            ]);
    }
}
