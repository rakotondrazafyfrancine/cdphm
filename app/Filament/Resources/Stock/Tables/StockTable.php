<?php

namespace App\Filament\Resources\Stock\Tables;

use App\Models\Lot;
use App\Services\Sorties;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Rechercher un lot, client, produit...')
            ->emptyStateHeading('Aucun lot en stock.')
            ->emptyStateIcon('lucide-inbox')
            ->columns([
                TextColumn::make('numero')->label('N° Lot')->weight('bold')->color('primary')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('id', $direction)),
                TextColumn::make('client.nom')->label('Client')->searchable(),
                TextColumn::make('espece')->label('Produit')->searchable(),
                TextColumn::make('poids_entree')->label('Poids (kg)')->numeric(2)->sortable(),
                TextColumn::make('poids_restant')->label('Poids restant')->badge()->color('success')
                    ->state(fn ($record) => $record->poids_restant ?? $record->poids_entree)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', ' ') . ' kg'),
                TextColumn::make('chambre.nom')->label('Chambre')->badge()->color('info'),
                TextColumn::make('date_entree_chambre')->label('Entrée chambre')->dateTime('d/m/y H:i')->sortable(),
                TextColumn::make('deja_sorti')->label('Sorties partielles')->badge()->color('warning')
                    ->state(function ($record) {
                        $faites = $record->sortiesChambre->where('statut', 'sortie_effectuee');

                        return $faites->isEmpty() ? null : $faites->count() . ' sortie' . ($faites->count() > 1 ? 's' : '') . ' · ' . number_format($faites->sum('poids_sorti'), 2, ',', ' ') . ' kg';
                    })
                    ->placeholder('-'),
                TextColumn::make('sortie')->label('Sortie')->badge()
                    ->state(fn ($record) => match (true) {
                        $record->sortiesChambre->contains('statut', 'paye') => 'À confirmer',
                        $record->sortiesChambre->contains('statut', 'en_attente_paiement') => 'Facture en attente',
                        default => null,
                    })
                    ->color(fn (?string $state) => $state === 'À confirmer' ? 'success' : 'warning')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('chambre_id')->label('Chambre')
                    ->relationship('chambre', 'nom'),
                Filter::make('sorties_partielles')->label('Clients avec sortie partielle')
                    ->toggle()
                    ->query(fn ($query) => $query->whereHas('sortiesChambre', fn ($q) => $q->where('statut', 'sortie_effectuee'))),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('historique')
                    ->label('Historique des sorties')
                    ->icon('lucide-history')
                    ->iconButton()
                    ->tooltip('Historique des sorties')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Sorties du lot ' . $record->numero . ' · ' . $record->client?->nom)
                    ->modalContent(fn ($record) => view('filament.stock.historique-sorties', ['lot' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalWidth('2xl'),
                Action::make('confirmer')
                    ->label('Confirmer sortie')
                    ->icon('lucide-package-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->sortiesChambre->contains('statut', 'paye'))
                    ->url(fn ($record) => route('sortiesChambre.confirmer', $record->sortiesChambre->firstWhere('statut', 'paye')->id), shouldOpenInNewTab: true),

                Action::make('sortir')
                    ->label('Sortir')
                    ->icon('lucide-package-open')
                    ->color('primary')
                    ->visible(fn ($record) => ! $record->sortiesChambre->contains('statut', 'paye'))
                    ->modalHeading(fn ($record) => 'Sortie du lot ' . $record->numero)
                    ->modalDescription('Une facture est créée. La sortie physique se confirme après le paiement à la caisse.')
                    ->modalSubmitActionLabel('Créer la facture')
                    ->modalWidth('md')
                    ->schema(fn ($record) => [
                        Text::make(fn () => $record->client?->nom . ' · ' . $record->espece . ' · ' . $record->chambre?->nom)->color('gray'),
                        TextInput::make('poids_sortie')
                            ->label('Poids à sortir')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(fn () => (float) ($record->poids_restant ?? $record->poids_entree))
                            ->step(0.01)
                            ->suffix('kg')
                            ->prefixIcon('lucide-scale')
                            ->default(fn () => (float) ($record->poids_restant ?? $record->poids_entree))
                            ->helperText(fn () => 'Disponible : ' . number_format((float) ($record->poids_restant ?? $record->poids_entree), 2, ',', ' ') . ' kg')
                            ->live(onBlur: true)
                            ->required(),
                        Text::make(function (Get $get) use ($record) {
                            [$jours, $tarif] = self::joursEtTarif($record);
                            $montant = (float) $get('poids_sortie') * $jours * $tarif;

                            return $jours . ' jour(s) x ' . number_format($tarif, 0, ',', ' ') . ' Ar/kg/jour : ' . number_format($montant, 0, ',', ' ') . ' Ar';
                        })->weight('bold')->color('primary'),
                    ])
                    ->action(function ($record, array $data) {
                        $disponible = (float) ($record->poids_restant ?? $record->poids_entree);
                        $poids = (float) $data['poids_sortie'];

                        if ($poids <= 0 || $poids > $disponible) {
                            Notification::make()->title('Poids invalide')->danger()->send();

                            return;
                        }

                        $montant = Sorties::sortirChambre($record, $poids)->montant;

                        Notification::make()
                            ->title('Facture créée pour ' . $record->numero)
                            ->body(number_format($montant, 0, ',', ' ') . ' Ar à payer à la caisse.')
                            ->success()->send();
                    }),
            ]);
    }

    /**
     * Jours de stockage (minimum 1) et tarif appliqué : même règle que la facture de sortie existante.
     *
     * @return array{0:int,1:float}
     */
    private static function joursEtTarif(Lot $lot): array
    {
        $jours = 0;
        if ($lot->date_entree_chambre) {
            $jours = Carbon::parse($lot->date_entree_chambre)->diffInDays(now(), absolute: true);
            $jours = max((int) ceil($jours), 1);
        }

        return [$jours, (float) ($lot->tarif_applique ?? Lot::tarifChambre())];
    }
}
