<?php

namespace App\Filament\Widgets;

use App\Models\Lot;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DerniersLots extends TableWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    // pas de rafraîchissement automatique toutes les 5 secondes
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Activité récente';

    public function table(Table $table): Table
    {
        return $table
            ->query(Lot::query()->with('client')->where('statut', '!=', 'sorti')->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('numero')->label('N°'),
                TextColumn::make('client.nom')->label('Client'),
                TextColumn::make('espece')->label('Espèce'),
                TextColumn::make('type_infrastructure')->label('Infrastructure')->badge(),
                TextColumn::make('poids_entree')->label('Poids (kg)')->numeric(2),
                TextColumn::make('statut')->badge(),
                TextColumn::make('created_at')->label('Entrée')->since(),
            ]);
    }
}
