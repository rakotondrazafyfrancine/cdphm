<?php

namespace App\Filament\Resources\Tunnels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TunnelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tunnel')
                    ->description('Tunnel de congélation et sa capacité maximale.')
                    ->icon('lucide-snowflake')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom')
                            ->placeholder('Ex. : Tunnel 1')
                            ->prefixIcon('lucide-snowflake')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('capacite_tonnes')
                            ->label('Capacité')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('tonnes')
                            ->prefixIcon('lucide-gauge')
                            ->helperText(fn ($state) => is_numeric($state) ? number_format($state * 1000, 0, ',', ' ') . ' kg' : 'Capacité maximale en tonnes.')
                            ->live(onBlur: true)
                            ->required(),
                    ]),
            ]);
    }
}
