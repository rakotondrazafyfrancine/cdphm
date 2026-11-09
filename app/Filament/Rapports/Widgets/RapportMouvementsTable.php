<?php

namespace App\Filament\Rapports\Widgets;

use App\Models\Mouvement;
use App\Support\RapportFiltres;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class RapportMouvementsTable extends TableWidget
{
    // affiché avec la page : pas de requête supplémentaire par widget
    protected static ?bool $isLazyLoaded = false;

    use InteractsWithPageFilters;

    protected static ?string $heading = 'Liste des mouvements (entrées et sorties)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Mouvement::requete(RapportFiltres::lots($this->pageFilters)))
            ->defaultSort('date_mvt', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50, 100])
            ->searchPlaceholder('Rechercher un client, un produit...')
            ->emptyStateHeading('Aucun mouvement trouvé.')
            ->emptyStateIcon('lucide-package-search')
            ->columns([
                TextColumn::make('date_mvt')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('type')->label('Mouvement')->badge()
                    ->color(fn (string $state) => match (true) {
                        $state === 'Entrée' => 'primary',
                        str_contains($state, 'partielle') => 'warning',
                        str_contains($state, 'chambre') => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('lot_id')->label('N° Lot')->weight('bold')->color('primary')
                    ->formatStateUsing(fn ($state) => 'LOT-' . str_pad((string) $state, 4, '0', STR_PAD_LEFT))->sortable(),
                TextColumn::make('client_nom')->label('Client')->searchable()->sortable(),
                TextColumn::make('produit')->label('Produit')->searchable()->placeholder('-'),
                TextColumn::make('poids')->label('Poids (kg)')->numeric(2)->weight('bold')->sortable()
                    ->description(fn ($record) => $record->reste !== null ? 'Reste en chambre : ' . number_format((float) $record->reste, 2, ',', ' ') . ' kg' : null),
                TextColumn::make('montant')->label('Montant (Ar)')->numeric(0)->placeholder('-'),
                TextColumn::make('emplacement')->label('Emplacement')->placeholder('-'),
                TextColumn::make('statut')->label('Statut')->badge()
                    ->formatStateUsing(fn (?string $s) => match ($s) {
                        'en_congelation' => 'En congélation',
                        'en_stock' => 'En stock',
                        'sorti' => 'Sorti',
                        'en_attente_paiement' => 'En attente de paiement',
                        'paye' => 'Payée',
                        'sortie_effectuee' => 'Sortie effectuée',
                        default => (string) $s,
                    })
                    ->color(fn (?string $s) => match ($s) {
                        'sorti', 'sortie_effectuee' => 'danger',
                        'en_congelation', 'en_attente_paiement' => 'warning',
                        'en_stock', 'paye' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')->label('Mouvement')
                    ->options([
                        'Entrée' => 'Entrées',
                        'Sortie partielle (chambre)' => 'Sorties partielles de chambre',
                        'Sortie totale (chambre)' => 'Sorties totales de chambre',
                        'Sortie tunnel' => 'Sorties de tunnel',
                    ]),
            ]);
    }
}
