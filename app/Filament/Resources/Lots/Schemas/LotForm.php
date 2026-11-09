<?php

namespace App\Filament\Resources\Lots\Schemas;

use App\Models\ChambreFroide;
use App\Models\Client;
use App\Models\ClientProduit;
use App\Models\Lot;
use App\Models\Tunnel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LotForm
{
    /** Valeur spéciale de la liste : saisie d'un nouveau produit. */
    public const AUTRE = '__autre__';

    /**
     * Produits déjà apportés par le client (fiche produits + lots), puis « Autre » en bas.
     *
     * @return array<string, string>
     */
    public static function optionsProduits($clientId): array
    {
        if (blank($clientId)) {
            return [];
        }

        $noms = Client::with(['produits', 'lots:id,client_id,espece'])->find($clientId)?->produitsApportes() ?? [];

        // les produits du client, puis « Autre » tout en bas de la liste
        return (array_combine($noms, $noms) ?: []) + [self::AUTRE => 'Autre (nouveau produit)'];
    }

    /**
     * Tunnels qui ne sont pas pleins (poids en congélation < capacité). Un tunnel plein réapparaît dès qu'il se libère.
     * $inclure : l'emplacement actuel d'un lot en modification, toujours proposé.
     *
     * @return array<int, string>
     */
    public static function tunnelsDisponibles(?int $inclure = null): array
    {
        return Tunnel::orderBy('nom')->get()
            ->filter(function (Tunnel $tunnel) use ($inclure) {
                $capacite = (float) $tunnel->capacite_tonnes * 1000;
                if ($tunnel->id === $inclure || $capacite <= 0) {
                    return true;
                }

                $charge = (float) Lot::where('tunnel_id', $tunnel->id)->where('statut', 'en_congelation')->sum('poids_entree');

                return $charge < $capacite;
            })
            ->pluck('nom', 'id')->all();
    }

    /**
     * Chambres qui ne sont pas pleines (poids en stock < capacité).
     *
     * @return array<int, string>
     */
    public static function chambresDisponibles(?int $inclure = null): array
    {
        return ChambreFroide::orderBy('nom')->get()
            ->filter(function (ChambreFroide $chambre) use ($inclure) {
                if ($chambre->id === $inclure || $chambre->capacite_kg <= 0) {
                    return true;
                }

                return $chambre->poids_stocke < $chambre->capacite_kg;
            })
            ->pluck('nom', 'id')->all();
    }

    /** Valeur de l'adresse (?tunnel_id=…) seulement si elle fait partie des emplacements disponibles. */
    private static function parDefaut(array $disponibles, $valeur): ?int
    {
        return filled($valeur) && array_key_exists((int) $valeur, $disponibles) ? (int) $valeur : null;
    }

    /** Nom du produit à enregistrer, que l'utilisateur l'ait choisi ou saisi. */
    public static function nomProduit(array $data): string
    {
        return trim((string) ($data['espece'] ?? ''));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(self::blocPrincipal());
    }

    /**
     * Bloc d'un produit avec le client : la petite zone « Nouveau produit » est en haut (colonne du produit),
     * puis le client à gauche et le produit à droite, le poids, puis tunnel et chambre.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private static function blocPrincipal(): array
    {
        $champs = self::champsProduit('client_id', true);

        return [
            self::champClient(),
            // côté produit (colonne de droite) : petite zone « nouveau produit » au-dessus du champ Produit
            Group::make([$champs[0], $champs[1]])->columnSpan(1),
            ...array_slice($champs, 2),
        ];
    }

    /** Liste du client : petite, avec création rapide d'un client. */
    private static function champClient(): Select
    {
        return Select::make('client_id')
            ->label('Client')
            ->options(fn () => Client::orderBy('nom')->pluck('nom', 'id')->all())
            ->searchable()
            ->native(false)
            ->placeholder('')
            ->live()
            ->afterStateUpdated(fn (Set $set, Get $get) => $set('espece', null))
            ->required()
            ->columnSpan(['default' => 1, 'md' => 1])
            ->createOptionForm([
                TextInput::make('nom')->label('Nom')->required()->maxLength(255),
                TextInput::make('contact')->label('Téléphone')->tel(),
                ToggleButtons::make('type')->label('Type')->inline()->default('collecteur')->required()
                    ->options(['collecteur' => 'Collecteur', 'societe_peche' => 'Société de pêche']),
            ])
            ->createOptionUsing(fn (array $data) => Client::create($data)->getKey())
            ->createOptionModalHeading('Nouveau client');
    }

    /**
     * Champs d'un produit : petite zone « Nouveau produit » en haut (si « Autre »), produit, poids, tunnel, chambre.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private static function champsProduit(string $cheminClient, bool $principal = false): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 3])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'cd-nouveau-produit'])
                ->visible(fn (Get $get) => $get('espece') === self::AUTRE)
                ->schema([
                    TextInput::make('nouveau_produit')
                        ->hiddenLabel()
                        ->placeholder('Nom du nouveau produit')
                        ->maxLength(50)
                        ->dehydrated(false)
                        ->columnSpan(['default' => 1, 'md' => 2]),

                    Actions::make([
                        Action::make('creerProduit')
                            ->label('Créer')
                            ->icon('lucide-check')
                            ->action(function (Get $get, Set $set) use ($cheminClient) {
                                $nom = trim((string) $get('nouveau_produit'));
                                if ($nom === '') {
                                    Notification::make()->title('Saisissez le nom du nouveau produit.')->warning()->send();

                                    return;
                                }
                                if (filled($clientId = $get($cheminClient))) {
                                    ClientProduit::firstOrCreate(['client_id' => $clientId, 'nom' => $nom]);
                                }
                                // le produit créé apparaît aussitôt dans le champ Produit
                                $set('espece', $nom);
                                $set('nouveau_produit', null);
                            }),
                    ])->extraAttributes(['style' => 'align-self: center']),
                ]),

            Select::make('espece')
                ->label('Produit')
                ->options(fn (Get $get) => self::optionsProduits($get($cheminClient)))
                ->getOptionLabelUsing(fn ($value) => $value === self::AUTRE ? 'Autre (nouveau produit)' : $value)
                ->searchable()
                ->native(false)
                ->placeholder('')
                ->live()
                ->disabled(fn (Get $get) => blank($get($cheminClient)))
                ->required()
                ->columnStart($principal ? null : 1)
                // « Autre » demande d'abord de saisir puis de créer le nouveau produit
                ->rules([fn () => function (string $attribute, $value, \Closure $fail) {
                    if ($value === self::AUTRE) {
                        $fail('Saisissez le nouveau produit puis cliquez sur « Créer ».');
                    }
                }]),

            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('poids_entree')
                        ->label('Poids (kg)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->required(),

                    Select::make('tunnel_id')
                        ->label('Tunnel')
                        ->options(fn (?Lot $record) => self::tunnelsDisponibles($record?->tunnel_id))
                        ->default(fn () => self::parDefaut(self::tunnelsDisponibles(), request()->query('tunnel_id')))
                        ->native(false)
                        ->placeholder('')
                        ->live()
                        ->required(fn (Get $get) => blank($get('chambre_id'))),

                    Select::make('chambre_id')
                        ->label('Chambre')
                        ->options(fn (?Lot $record) => self::chambresDisponibles($record?->chambre_id))
                        ->default(fn () => self::parDefaut(self::chambresDisponibles(), request()->query('chambre_id')))
                        ->native(false)
                        ->placeholder('')
                        ->live()
                        ->required(fn (Get $get) => blank($get('tunnel_id'))),
                ]),

        ];
    }

    /**
     * Nouvelle entrée : le premier produit est dans le bloc principal, avec le client à gauche du produit.
     * « Ajouter un produit » ajoute ensuite d'autres blocs (produit, poids, tunnel, chambre).
     */
    public static function configureEntree(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->extraAttributes(['class' => 'cd-bloc-principal'])
                    ->schema(self::blocPrincipalEntree()),

                Repeater::make('lignes')
                    ->hiddenLabel()
                    ->defaultItems(0)
                    ->addable(false) // « Ajouter un produit » est dans la barre d'actions, à côté d'Enregistrer
                    ->extraAttributes(['class' => 'cd-lignes'])
                    ->columns(2)
                    ->schema(self::champsProduit('../../client_id')),
            ]);
    }

    /** Bloc principal de la nouvelle entrée : même disposition, avec la remise à zéro des produits si le client change. */
    private static function blocPrincipalEntree(): array
    {
        $champs = self::champsProduit('client_id', true);

        return [
            self::champClient()
                ->afterStateUpdated(fn (Set $set, Get $get) => [
                    $set('espece', null),
                    $set('lignes', collect($get('lignes') ?? [])
                        ->map(fn ($ligne) => array_merge($ligne, ['espece' => null]))->all()),
                ]),
            // côté produit (colonne de droite) : petite zone « nouveau produit » au-dessus du champ Produit
            Group::make([$champs[0], $champs[1]])->columnSpan(1),
            ...array_slice($champs, 2),
        ];
    }
}
