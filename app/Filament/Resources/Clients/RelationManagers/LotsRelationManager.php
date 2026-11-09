<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LotsRelationManager extends RelationManager
{
    protected static string $relationship = 'lots';

    protected static ?string $title = 'Historique des lots';

    protected static string|\BackedEnum|null $icon = 'lucide-history';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['tunnel', 'chambre']))
            ->defaultSort('id', 'desc')
            ->emptyStateHeading('Aucun lot pour ce client.')
            ->emptyStateIcon('lucide-package-search')
            ->columns([
                TextColumn::make('numero')->label('N° Lot')->weight('bold')->color('primary')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('id', $direction)),
                TextColumn::make('espece')->label('Produit')->placeholder('-'),
                TextColumn::make('emplacement')->label('Emplacement')->badge()->color('info')
                    ->state(fn ($record) => $record->chambre?->nom ?? $record->tunnel?->nom)
                    ->placeholder('-'),
                TextColumn::make('poids_entree')->label('Poids entré (kg)')->numeric(2)->sortable(),
                TextColumn::make('poids_restant')->label('Poids restant (kg)')
                    ->state(fn ($record) => $record->statut === 'sorti' ? 0 : ($record->poids_restant ?? $record->poids_entree))
                    ->numeric(2),
                TextColumn::make('statut')->label('Statut')->badge()
                    ->formatStateUsing(fn (?string $s) => match ($s) {
                        'sorti' => 'Sorti',
                        'en_congelation' => 'En congélation',
                        'en_stock' => 'En stock',
                        default => ucfirst((string) $s),
                    })
                    ->color(fn (?string $s) => match ($s) {
                        'sorti' => 'danger',
                        'en_congelation' => 'warning',
                        'en_stock' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('date_entree')->label('Date entrée')->sortable(query: fn ($query, $direction) => $query->orderBy('created_at', $direction))
                    ->state(fn ($record) => $record->created_at?->format('d/m/Y H:i')),
                TextColumn::make('date_sortie')->label('Date sortie')->dateTime('d/m/Y H:i')->sortable()->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('statut')->label('Statut')
                    ->options(['en_congelation' => 'En congélation', 'en_stock' => 'En stock', 'sorti' => 'Sorti']),
            ]);
    }
}
