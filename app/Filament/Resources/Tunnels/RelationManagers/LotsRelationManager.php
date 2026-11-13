<?php

namespace App\Filament\Resources\Tunnels\RelationManagers;

use App\Filament\Resources\Lots\LotResource;
use App\Models\SortieTunnel;
use App\Services\Sorties;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
            ->recordClasses(fn ($record) => $this->getOwnerRecord()->etat() === 'pret' ? 'cd-ligne-alerte' : null)
            ->columns([
                TextColumn::make('numero')->label('N° Lot')->weight('bold'),
                TextColumn::make('client.nom')->label('Client')->placeholder('-'),
                TextColumn::make('espece')->label('Produit')->placeholder('-'),
                TextColumn::make('poids_entree')->label('Quantité (kg)')->numeric(2),
                TextColumn::make('created_at')->label('Date entrée')->date('d/m/Y'),
                TextColumn::make('depuis')->label('En tunnel depuis')->badge()->color('gray')
                    ->state(fn () => $this->getOwnerRecord()->estDemarre() ? $this->getOwnerRecord()->demarre_le->diffForHumans() : 'Pas encore démarré'),
                TextColumn::make('alerte')->label('État')->badge()
                    ->state(fn () => match ($this->getOwnerRecord()->etat()) {
                        'chargement' => 'En chargement',
                        'ferme' => 'Fermé jusqu\'à ' . $this->getOwnerRecord()->ouvrableLe()->format('d/m H:i'),
                        'pret' => 'Prêt à ouvrir',
                    })
                    ->color(fn (string $state) => $state === 'Prêt à ouvrir' ? 'danger' : 'gray')
                    ->icon(fn (string $state) => $state === 'Prêt à ouvrir' ? 'lucide-triangle-alert' : ($state === 'En chargement' ? 'lucide-package-plus' : 'lucide-lock')),
            ])
            ->headerActions([
                Action::make('ajouter')
                    ->label('Ajouter un lot')
                    ->icon('lucide-plus')
                    ->visible(fn () => ! $this->getOwnerRecord()->estDemarre())
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
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => 'Confirmer la sortie du lot ' . $record->numero . ' ?')
                    ->modalDescription('Le client a payé à la caisse : les produits lui sont remis.')
                    ->action(function ($record) {
                        Sorties::confirmerTunnel($record->sortiesTunnel->firstWhere('statut', 'paye'));
                        Notification::make()->title('Sortie confirmée')->success()->send();
                    }),
                Action::make('sortir')
                    ->label('Bon de sortie')
                    ->icon('lucide-package-open')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->sortiesTunnel->contains(fn ($s) => in_array($s->statut, ['en_attente_paiement', 'paye'])))
                    ->disabled(fn () => ! $this->getOwnerRecord()->peutEtreOuvert())
                    ->tooltip(fn () => $this->getOwnerRecord()->peutEtreOuvert() ? null : 'Tunnel fermé jusqu\'au ' . $this->getOwnerRecord()->ouvrableLe()->format('d/m à H:i'))
                    ->modalHeading(fn ($record) => 'Bon de sortie du lot ' . $record->numero)
                    ->modalDescription('Saisis le poids constaté à la contre-pesée. Le client porte ensuite le bon à la caisse.')
                    ->modalSubmitActionLabel('Créer le bon de sortie')
                    ->schema(fn ($record) => [
                        TextInput::make('poids_constate')
                            ->label('Poids constaté (kg)')
                            ->numeric()->minValue(0.01)->step(0.01)->suffix('kg')
                            ->default((float) $record->poids_entree)
                            ->helperText('Poids enregistré : ' . number_format((float) $record->poids_entree, 2, ',', ' ') . ' kg. Ce poids figure sur le bon et la caisse facture ce poids.')
                            ->required(),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        if ($message = Sorties::tunnelFerme($record)) {
                            Notification::make()->title('Tunnel fermé')->body($message)->warning()->send();

                            return;
                        }
                        $sortie = Sorties::sortirTunnel($record, (float) $data['poids_constate']);
                        Notification::make()->title('Bon de sortie créé')->body('Le client le porte à la caisse.')->success()->send();
                        $livewire->js('window.open(' . json_encode(route('sorties.bonGroupe', ['tunnel' => $sortie->id])) . ', "_blank")');
                    }),
            ]);
    }
}
