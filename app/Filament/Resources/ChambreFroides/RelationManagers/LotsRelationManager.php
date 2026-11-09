<?php

namespace App\Filament\Resources\ChambreFroides\RelationManagers;

use App\Filament\Resources\Lots\LotResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LotsRelationManager extends RelationManager
{
    protected static string $relationship = 'lots';

    protected static ?string $title = 'Lots dans cette chambre';

    protected static string|\BackedEnum|null $icon = 'lucide-clipboard-list';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', '!=', 'sorti')->with('client')->latest('id'))
            ->paginated(false)
            ->emptyStateHeading('Aucun lot dans cette chambre pour le moment.')
            ->emptyStateIcon('lucide-thermometer-snowflake')
            ->columns([
                TextColumn::make('numero')->label('N° Lot')->weight('bold'),
                TextColumn::make('client.nom')->label('Client')->placeholder('-'),
                TextColumn::make('espece')->label('Produit')->placeholder('-'),
                TextColumn::make('poids_chambre')->label('Poids en chambre (kg)')->weight('bold')
                    ->state(fn ($record) => $record->poids_restant ?? $record->poids_entree)->numeric(0),
                TextColumn::make('created_at')->label("Date d'entrée")->date('d/m/Y'),
                TextColumn::make('depuis')->label('Depuis')->badge()->color('gray')
                    ->state(fn ($record) => $record->created_at?->diffForHumans()),
            ])
            ->headerActions([
                Action::make('ajouter')
                    ->label('Ajouter un lot')
                    ->icon('lucide-plus')
                    ->url(fn () => LotResource::getUrl('create', ['chambre_id' => $this->getOwnerRecord()->id])),
            ])
            ->recordActionsPosition(RecordActionsPosition::AfterColumns)
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('bon')
                    ->label("Bon d'entrée")
                    ->icon('lucide-printer')
                    ->color('info')
                    ->url(fn ($record) => route('lots.print', $record->id), shouldOpenInNewTab: true),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon('lucide-trash-2')
                    ->modalHeading('Supprimer définitivement ce lot ?')
                    ->successNotificationTitle('Lot supprimé'),
            ]);
    }
}
