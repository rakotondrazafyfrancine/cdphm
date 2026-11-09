<?php

namespace App\Filament\Resources\Produits;

use App\Filament\Resources\Produits\Pages\CreateProduit;
use App\Filament\Resources\Produits\Pages\EditProduit;
use App\Filament\Resources\Produits\Pages\ListProduits;
use App\Filament\Resources\Produits\Schemas\ProduitForm;
use App\Filament\Resources\Produits\Tables\ProduitsTable;
use App\Models\Produit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProduitResource extends Resource
{
    protected static ?string $model = Produit::class;

    protected static ?string $modelLabel = 'produit';

    protected static ?string $pluralModelLabel = 'produits';

    protected static ?string $navigationLabel = 'Produits';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Données de base';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-shopping-basket';

    public static function form(Schema $schema): Schema
    {
        return ProduitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProduitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProduits::route('/'),
            'create' => CreateProduit::route('/create'),
            'edit' => EditProduit::route('/{record}/edit'),
        ];
    }
}
