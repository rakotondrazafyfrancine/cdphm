<?php

namespace App\Filament\Resources\Produits\Tables;

use App\Models\Produit;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProduitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nom')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Aucun produit enregistré.')
            ->emptyStateIcon('lucide-shopping-basket')
            ->columns([
                TextColumn::make('nom')->label('Produit')->weight('bold')->icon('lucide-tag')->iconColor('primary')
                    ->searchable()->sortable(),
                TextColumn::make('categorie')->label('Catégorie')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : null)
                    ->placeholder('-')->searchable()->sortable(),
                TextColumn::make('updated_at')->label('Dernière modification')->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categorie')->label('Catégorie')
                    ->options(fn () => Produit::query()->whereNotNull('categorie')->distinct()->orderBy('categorie')->pluck('categorie', 'categorie')->all()),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                EditAction::make()->label('Modifier')->icon('lucide-pencil')->iconButton()->tooltip('Modifier')->color('primary'),
                DeleteAction::make()->label('Supprimer')->icon('lucide-trash-2')->iconButton()->tooltip('Supprimer')
                    ->modalHeading(fn ($record) => 'Supprimer le produit « ' . $record->nom . ' » ?')
                    ->before(function ($record, DeleteAction $action) {
                        // même règle que l'ancien écran : un produit utilisé dans des lots ne se supprime pas
                        if ($record->lots()->exists()) {
                            Notification::make()->title('Suppression impossible')
                                ->body('Ce produit est utilisé dans des lots.')->danger()->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }
}
