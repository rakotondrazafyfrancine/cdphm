<?php

namespace App\Filament\Resources\Lots\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Forms\Components\TextInput;
use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use App\Services\Sorties;
use Filament\Tables\Table;

class LotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // un lot sorti disparaît de ce tableau ; il reste dans l'historique du client et dans les rapports
            ->modifyQueryUsing(fn ($query) => $query->where('statut', '!=', 'sorti')->with(['client', 'tunnel', 'chambre']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('numero')->label('N°')->sortable(query: fn ($query, $direction) => $query->orderBy('id', $direction)),
                TextColumn::make('client.nom')->label('Client')->searchable(),
                TextColumn::make('espece')->label('Espèce')->searchable(),
                TextColumn::make('type_infrastructure')->label('Infrastructure')->badge(),
                TextColumn::make('nom_infrastructure')->label('Emplacement'),
                TextColumn::make('poids_entree')->label('Poids (kg)')->numeric(2)->sortable(),
                TextColumn::make('statut')->badge()
                    ->color(fn (?string $state) => $state === 'en_stock' ? 'info' : 'gray'),
                TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type_infrastructure')->label('Infrastructure')
                    ->options(['tunnel' => 'Tunnel', 'chambre' => 'Chambre'])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'chambre' => $query->whereNotNull('chambre_id'),
                        'tunnel' => $query->whereNull('chambre_id')->whereNotNull('tunnel_id'),
                        default => $query,
                    }),
                SelectFilter::make('statut')
                    ->options(['en_stock' => 'En stock', 'en_congelation' => 'En congélation']),
                SelectFilter::make('client_id')->label('Client')
                    ->relationship('client', 'nom')->searchable(),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('bon')
                    ->label("Bon d'entrée")
                    ->icon('lucide-printer')
                    ->iconButton()
                    ->tooltip("Bon d'entrée")
                    ->color('gray')
                    ->url(fn ($record) => route('lots.print', $record->id), shouldOpenInNewTab: true),
                EditAction::make()
                    ->label('Modifier')
                    ->icon('lucide-pencil')
                    ->iconButton()
                    ->tooltip('Modifier')
                    ->color('primary')
                    ->visible(fn ($record) => $record->statut !== 'sorti'),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon('lucide-trash-2')
                    ->iconButton()
                    ->tooltip('Supprimer')
                    ->modalHeading(fn ($record) => 'Supprimer le lot ' . $record->numero . ' ?')
                    ->modalDescription('Cette action est définitive : le lot et ses sorties associées seront supprimés.')
                    ->successNotificationTitle('Lot supprimé'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bonGroupe')
                        ->label("Bon d'entrée groupé")
                        ->icon('lucide-printer')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, $livewire) {
                            if (! self::memeClient($records)) {
                                return;
                            }
                            self::ouvrir($livewire, route('lots.printMultiple', ['ids' => $records->pluck('id')->implode(',')]));
                        }),

                    BulkAction::make('sortirGroupe')
                        ->label('Sortir (bon de sortie)')
                        ->icon('lucide-package-open')
                        ->color('primary')
                        ->modalHeading('Bon de sortie des lots sélectionnés')
                        ->modalDescription('Le bon de sortie est remis au client, qui le porte à la caisse. Un seul bon pour tous les lots, en tunnel ou en chambre.')
                        ->modalSubmitActionLabel('Créer le bon de sortie')
                        ->modalWidth('lg')
                        ->schema(fn (Collection $records) => $records
                            ->filter(fn ($lot) => $lot->statut !== 'sorti')
                            ->map(function ($lot) {
                                $restant = (float) ($lot->poids_restant ?? $lot->poids_entree);
                                $label = $lot->numero . ' · ' . $lot->espece . ' · ' . ($lot->chambre_id ? 'chambre ' . $lot->chambre?->nom : 'tunnel ' . $lot->tunnel?->nom);

                                return $lot->chambre_id
                                    ? TextInput::make('poids_' . $lot->id)
                                        ->label($label)
                                        ->numeric()->minValue(0.01)->maxValue($restant)->step(0.01)->suffix('kg')
                                        ->default($restant)
                                        ->helperText('Reste : ' . number_format($restant, 2, ',', ' ') . ' kg (sortie partielle possible)')
                                        ->required()
                                    : TextInput::make('tunnel_' . $lot->id)
                                        ->label($label)
                                        ->numeric()->minValue(0.01)->step(0.01)->suffix('kg')
                                        ->default($restant)
                                        ->helperText('Contre-pesée : poids enregistré ' . number_format($restant, 2, ',', ' ') . ' kg. Ce poids figure sur le bon et la caisse facture ce poids.')
                                        ->required();
                            })->values()->all())
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data, $livewire) {
                            if (! self::memeClient($records)) {
                                return;
                            }

                            $chambre = [];
                            $tunnel = [];
                            $ignores = [];

                            foreach ($records as $lot) {
                                if ($lot->statut === 'sorti' || Sorties::aUneSortieEnCours($lot)) {
                                    $ignores[] = $lot->numero;

                                    continue;
                                }

                                if ($lot->chambre_id && $lot->statut === 'en_stock') {
                                    $poids = (float) ($data['poids_' . $lot->id] ?? 0);
                                    if ($poids <= 0) {
                                        $ignores[] = $lot->numero;

                                        continue;
                                    }
                                    $chambre[] = Sorties::sortirChambre($lot, $poids)->id;
                                } elseif ($lot->tunnel_id && $lot->statut === 'en_congelation') {
                                    if ($message = Sorties::tunnelFerme($lot)) {
                                        Notification::make()->title($lot->numero . ' : tunnel fermé')->body($message)->warning()->send();
                                        $ignores[] = $lot->numero;

                                        continue;
                                    }
                                    $tunnel[] = Sorties::sortirTunnel($lot, (float) ($data['tunnel_' . $lot->id] ?? 0))->id;
                                } else {
                                    $ignores[] = $lot->numero;
                                }
                            }

                            if ($ignores) {
                                Notification::make()->title('Lots ignorés : ' . implode(', ', $ignores))
                                    ->body('Déjà sortis ou avec une facture en cours.')->warning()->send();
                            }

                            if (! $chambre && ! $tunnel) {
                                return;
                            }

                            Notification::make()->title('Bon de sortie créé')
                                ->body('Le client le porte à la caisse, où la facture est établie puis payée.')->success()->send();
                            self::ouvrir($livewire, route('sorties.bonGroupe', array_filter([
                                'chambre' => implode(',', $chambre),
                                'tunnel' => implode(',', $tunnel),
                            ])));
                        }),

                    BulkAction::make('encaisserGroupe')
                        ->label('Marquer payées')
                        ->icon('lucide-circle-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmer le paiement des factures de ces lots ?')
                        ->modalDescription(fn (Collection $records) => number_format(self::sortiesDe($records, Sorties::EN_ATTENTE)->sum('montant'), 0, ',', ' ') . ' Ar seront enregistrés comme payés.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            if (! self::memeClient($records)) {
                                return;
                            }

                            $sorties = self::sortiesDe($records, Sorties::EN_ATTENTE);
                            $sorties->each(fn ($x) => $x->update(['statut' => Sorties::PAYE]));

                            Notification::make()
                                ->title($sorties->count() ? $sorties->count() . ' facture(s) marquée(s) comme payée(s)' : 'Aucune facture en attente pour ces lots')
                                ->{$sorties->count() ? 'success' : 'warning'}()->send();
                        }),

                    BulkAction::make('confirmerGroupe')
                        ->label('Confirmer la sortie (client payé)')
                        ->icon('lucide-package-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmer la sortie physique des lots payés ?')
                        ->modalDescription('Le client a payé à la caisse : les produits lui sont remis et les lots sortent du stock.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            if (! self::memeClient($records)) {
                                return;
                            }

                            $sorties = self::sortiesDe($records, Sorties::PAYE);

                            if ($sorties->isEmpty()) {
                                Notification::make()->title('Aucune facture payée à sortir')
                                    ->body('Marque d\'abord les factures comme payées.')->warning()->send();

                                return;
                            }

                            $sorties->each(fn ($x) => $x instanceof SortieChambre ? Sorties::confirmerChambre($x) : Sorties::confirmerTunnel($x));

                            Notification::make()->title($sorties->count() . ' sortie(s) confirmée(s)')->success()->send();
                        }),
                ])->label('Actions groupées'),
            ]);
    }

    /** Les lots choisis doivent appartenir au même client (un seul document par client). */
    private static function memeClient(Collection $lots): bool
    {
        if ($lots->pluck('client_id')->unique()->count() > 1) {
            Notification::make()->title('Choisis des lots du même client')->danger()->send();

            return false;
        }

        return true;
    }

    /** Ouvre un document dans un nouvel onglet. */
    private static function ouvrir($livewire, string $url): void
    {
        $livewire->js('window.open(' . json_encode($url) . ', "_blank")');
    }

    /** Sorties (chambre et tunnel) des lots donnés, dans un statut donné. */
    private static function sortiesDe(Collection $lots, string $statut): Collection
    {
        $ids = $lots->pluck('id');

        return SortieChambre::with('lot')->whereIn('lot_id', $ids)->where('statut', $statut)->get()
            ->concat(SortieTunnel::with('lot')->whereIn('lot_id', $ids)->where('statut', $statut)->get());
    }
}
