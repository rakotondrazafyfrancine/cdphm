<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informations')
                ->icon('lucide-user-round')
                ->columns(['default' => 1, 'md' => 4])
                ->schema([
                    TextEntry::make('nom')->label('Nom')->weight('bold')->icon('lucide-building-2'),
                    TextEntry::make('type')->label('Type')->badge()
                        ->formatStateUsing(fn (?string $state) => match ($state) {
                            'collecteur' => 'Collecteur',
                            'societe_peche' => 'Société de pêche',
                            default => $state,
                        }),
                    TextEntry::make('contact')->label('Contact')->icon('lucide-phone')->placeholder('-'),
                    TextEntry::make('adresse')->label('Adresse')->icon('lucide-map-pin')->placeholder('-'),
                ]),

            Section::make('Produits apportés')
                ->description('Tous les produits livrés par ce client.')
                ->icon('lucide-shopping-basket')
                ->schema([
                    TextEntry::make('produits_apportes')->hiddenLabel()->badge()->color('primary')
                        ->state(fn ($record) => $record->produitsApportes())
                        ->placeholder('Aucun produit enregistré.'),
                ]),

            Section::make('Résumé')
                ->icon('lucide-chart-column')
                ->columns(['default' => 2, 'md' => 4])
                ->schema([
                    TextEntry::make('nb_lots')->label('Lots reçus')->weight('bold')
                        ->state(fn ($record) => $record->lots()->count()),
                    TextEntry::make('poids_total')->label('Poids total reçu')->weight('bold')
                        ->state(fn ($record) => number_format((float) $record->lots()->sum('poids_entree'), 0, ',', ' ') . ' kg'),
                    TextEntry::make('en_cours')->label('Lots en cours')->weight('bold')
                        ->state(fn ($record) => $record->lots()->where('statut', '!=', 'sorti')->count()),
                    TextEntry::make('sortis')->label('Lots sortis')->weight('bold')
                        ->state(fn ($record) => $record->lots()->where('statut', 'sorti')->count()),
                ]),
        ]);
    }
}
