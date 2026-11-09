<?php

namespace App\Filament\Resources\Tarifs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class TarifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::components());
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function components(): array
    {
        return [
                Section::make('Modifier le tarif')
                    ->description('Le nouveau montant s\'applique tout de suite aux lots en cours et aux prochaines factures.')
                    ->icon('lucide-hand-coins')
                    ->schema([
                        Text::make(fn ($record) => $record?->libelle)->weight('bold')->size('lg'),
                        TextInput::make('montant')
                            ->label('Montant')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('Ar / kg / jour')
                            ->prefixIcon('lucide-banknote')
                            ->required(),
                    ]),
        ];
    }
}
