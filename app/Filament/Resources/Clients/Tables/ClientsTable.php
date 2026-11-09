<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['produits', 'lots:id,client_id,espece']))
            ->defaultSort('nom')
            ->recordUrl(fn ($record) => ClientResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('nom')->label('Nom')->weight('bold')->color('primary')->searchable()->sortable(),
                TextColumn::make('contact')->label('Contact')->searchable()->placeholder('-'),
                TextColumn::make('adresse')->label('Adresse')->searchable()->placeholder('-'),
                TextColumn::make('type')->label('Type')->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'collecteur' => 'Collecteur',
                        'societe_peche' => 'Société de pêche',
                        default => $state,
                    })
                    ->color(fn (?string $state) => $state === 'societe_peche' ? 'info' : 'primary'),
                TextColumn::make('produits_apportes')->label('Produits')->badge()->color('gray')
                    ->state(fn ($record) => $record->produitsApportes())
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('Aucun produit'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Type')
                    ->options(['collecteur' => 'Collecteur', 'societe_peche' => 'Société de pêche']),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                ViewAction::make()->label('Voir')->icon('lucide-eye')->iconButton()->tooltip('Voir')->color('info'),
                EditAction::make()->label('Modifier')->icon('lucide-pencil')->iconButton()->tooltip('Modifier')->color('primary'),
                DeleteAction::make()->label('Supprimer')->icon('lucide-trash-2')->iconButton()->tooltip('Supprimer')
                    ->modalHeading(fn ($record) => 'Supprimer le client « ' . $record->nom . ' » ?')
                    ->before(function ($record, DeleteAction $action) {
                        // la suppression en base effacerait aussi ses lots (cascade) : on la refuse
                        if ($record->lots()->exists()) {
                            Notification::make()->title('Suppression impossible')
                                ->body('Ce client a des lots enregistrés : il ne peut pas être supprimé.')
                                ->danger()->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }
}
