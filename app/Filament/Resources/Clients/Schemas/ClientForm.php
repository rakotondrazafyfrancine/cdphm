<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identité du client')
                    ->description('Nom et catégorie du client, tels qu\'ils apparaîtront sur les bons et factures.')
                    ->icon('lucide-user-round')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom / Raison sociale')
                            ->placeholder('Ex. : Pêcheries de Mahajanga')
                            ->prefixIcon('lucide-building-2')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        ToggleButtons::make('type')
                            ->label('Type de client')
                            ->options(['collecteur' => 'Collecteur', 'societe_peche' => 'Société de pêche'])
                            ->icons(['collecteur' => 'lucide-user-round', 'societe_peche' => 'lucide-ship'])
                            ->colors(['collecteur' => 'primary', 'societe_peche' => 'info'])
                            ->default('collecteur')
                            ->inline()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Coordonnées')
                    ->icon('lucide-contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact')
                            ->label('Téléphone')
                            ->placeholder('034 00 000 00')
                            ->tel()
                            ->prefixIcon('lucide-phone')
                            ->maxLength(255),
                        TextInput::make('adresse')
                            ->label('Adresse')
                            ->placeholder('Quartier, ville')
                            ->prefixIcon('lucide-map-pin')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
