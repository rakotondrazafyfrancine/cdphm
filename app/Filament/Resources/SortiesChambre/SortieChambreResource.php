<?php

namespace App\Filament\Resources\SortiesChambre;

use App\Filament\Resources\SortiesChambre\Pages\ListSortieChambres;
use App\Models\SortieChambre;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Collection;
use Filament\Tables\Table;

class SortieChambreResource extends Resource
{
    protected static ?string $model = SortieChambre::class;

    protected static ?string $modelLabel = 'sortie chambre';

    protected static ?string $pluralModelLabel = 'sorties chambre';

    protected static ?string $navigationLabel = 'Factures chambre';

    protected static ?int $navigationSort = 8;

    protected static string|\UnitEnum|null $navigationGroup = 'Opérations';

    protected static ?string $slug = 'factures-chambre';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-receipt';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('lot.numero')->label('N° Lot'),
                TextColumn::make('lot.client.nom')->label('Client')->searchable(),
                TextColumn::make('poids_sorti')->label('Poids (kg)')->numeric(2)->sortable(),
                TextColumn::make('jours')->label('Jours'),
                TextColumn::make('montant')->label('Montant (Ar)')->numeric(0)->sortable(),
                TextColumn::make('statut')->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'en_attente_paiement' => 'En attente',
                        'paye' => 'Payée',
                        'sortie_effectuee' => 'Sortie effectuée',
                        default => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'en_attente_paiement' => 'warning',
                        'paye' => 'info',
                        'sortie_effectuee' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('statut')->options([
                    'en_attente_paiement' => 'En attente',
                    'paye' => 'Payée',
                    'sortie_effectuee' => 'Sortie effectuée',
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('facture')
                    ->label('Facture')
                    ->icon('lucide-printer')
                    ->iconButton()
                    ->tooltip('Facture')
                    ->color('gray')
                    ->url(fn ($record) => route('sortiesChambre.facture', $record->id), shouldOpenInNewTab: true),
                Action::make('payer')
                    ->label('Marquer payée')
                    ->icon('lucide-circle-check')
                    ->iconButton()
                    ->tooltip('Marquer payée')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_attente_paiement')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer le paiement ?')
                    ->modalDescription(fn ($record) => 'Le paiement de ' . number_format($record->montant, 0, ',', ' ') . ' Ar pour le lot ' . $record->lot?->numero . ' sera enregistré. Le client pourra ensuite retourner voir le magasinier.')
                    ->modalSubmitActionLabel('Oui, marquer payée')
                    ->action(function ($record) {
                        if ($record->statut !== 'en_attente_paiement') {
                            Notification::make()->title("Cette facture n'est plus en attente de paiement.")->danger()->send();

                            return;
                        }

                        $record->update(['statut' => 'paye']);

                        Notification::make()->title('Facture marquée comme payée')
                            ->body('Le client peut retourner voir le magasinier.')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('factureGroupee')
                        ->label('Facture groupée')
                        ->icon('lucide-printer')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, $livewire) {
                            if ($records->map(fn ($r) => $r->lot->client_id)->unique()->count() > 1) {
                                Notification::make()->title('Choisis des factures du même client')->danger()->send();

                                return;
                            }
                            $livewire->js('window.open(' . json_encode(route('factures.groupee', ['chambre' => $records->pluck('id')->implode(',')])) . ', "_blank")');
                        }),
                    BulkAction::make('payerGroupe')
                        ->label('Marquer payées')
                        ->icon('lucide-circle-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmer le paiement des factures sélectionnées ?')
                        ->modalDescription(fn (Collection $records) => number_format($records->where('statut', 'en_attente_paiement')->sum('montant'), 0, ',', ' ') . ' Ar seront enregistrés comme payés.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $n = 0;
                            foreach ($records->where('statut', 'en_attente_paiement') as $r) {
                                $r->update(['statut' => 'paye']);
                                $n++;
                            }
                            Notification::make()->title($n . ' facture(s) marquée(s) comme payée(s)')->success()->send();
                        }),
                ])->label('Actions groupées'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSortieChambres::route('/'),
        ];
    }
}
