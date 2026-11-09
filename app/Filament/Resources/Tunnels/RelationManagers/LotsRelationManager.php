<?php

namespace App\Filament\Resources\Tunnels\RelationManagers;

use App\Filament\Resources\Lots\LotResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LotsRelationManager extends RelationManager
{
    protected static string $relationship = 'lots';

    protected static ?string $title = 'Lots en congélation';

    protected static string|\BackedEnum|null $icon = 'lucide-clipboard-list';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'en_congelation')->with(['client', 'sortiesTunnel'])->latest('id'))
            ->paginated(false)
            ->emptyStateHeading('Aucun lot en congélation.')
            ->emptyStateIcon('lucide-snowflake')
            ->recordClasses(fn ($record) => $record->created_at && $record->created_at->lt(now()->subHours(24)) ? 'cd-ligne-alerte' : null)
            ->columns([
                TextColumn::make('numero')->label('N° Lot')->weight('bold'),
                TextColumn::make('client.nom')->label('Client')->placeholder('-'),
                TextColumn::make('espece')->label('Produit')->placeholder('-'),
                TextColumn::make('poids_entree')->label('Quantité (kg)')->numeric(2),
                TextColumn::make('created_at')->label('Date entrée')->date('d/m/Y'),
                TextColumn::make('depuis')->label('Depuis')->badge()->color('gray')
                    ->state(fn ($record) => $record->created_at?->diffForHumans()),
                TextColumn::make('alerte')->label('Alerte')->badge()
                    ->state(fn ($record) => $record->created_at && $record->created_at->lt(now()->subHours(24)) ? '+24 h' : 'OK')
                    ->color(fn (string $state) => $state === 'OK' ? 'success' : 'danger')
                    ->icon(fn (string $state) => $state === 'OK' ? 'lucide-circle-check' : 'lucide-triangle-alert'),
            ])
            ->headerActions([
                Action::make('ajouter')
                    ->label('Ajouter un lot')
                    ->icon('lucide-plus')
                    ->url(fn () => LotResource::getUrl('create', ['tunnel_id' => $this->getOwnerRecord()->id])),
            ])
            ->recordActionsPosition(RecordActionsPosition::AfterColumns)
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('confirmer')
                    ->label('Confirmer sortie')
                    ->icon('lucide-package-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->sortiesTunnel->contains('statut', 'paye'))
                    ->url(fn ($record) => route('sortiesTunnel.confirmer', $record->sortiesTunnel->firstWhere('statut', 'paye')->id)),
                Action::make('sortir')
                    ->label('Sortir')
                    ->icon('lucide-package-open')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->sortiesTunnel->contains('statut', 'paye'))
                    ->requiresConfirmation()
                    ->modalHeading('Créer la facture de sortie ?')
                    ->modalDescription(fn ($record) => 'Une facture de sortie sera créée pour le lot ' . $record->numero . '.')
                    ->action(fn ($record) => redirect()->route('lots.demanderSortieTunnel', $record->id)),
            ]);
    }
}
