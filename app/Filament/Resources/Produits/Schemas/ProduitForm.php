<?php

namespace App\Filament\Resources\Produits\Schemas;

use App\Models\Produit;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProduitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required(fn (Get $get) => blank($get('categorie')))
                    ->maxLength(255)
                    ->live(onBlur: true),
                TextInput::make('categorie')
                    ->label('Catégorie')
                    ->required(fn (Get $get) => blank($get('nom')))
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->datalist(fn () => Produit::query()->whereNotNull('categorie')->distinct()->orderBy('categorie')->pluck('categorie')->all()),
            ]);
    }
}
