<?php

namespace App\Filament\Resources\Stock;

use App\Filament\Resources\Stock\Pages\ListStock;
use App\Filament\Resources\Stock\Tables\StockTable;
use App\Models\Lot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Lot::class;

    protected static ?string $modelLabel = 'lot en stock';

    protected static ?string $pluralModelLabel = 'stock';

    protected static ?string $navigationLabel = 'Stock';

    protected static ?string $slug = 'stock';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Opérations';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-boxes';

    /** Lots actuellement en chambre froide. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('chambre_id')
            ->where('statut', 'en_stock')
            ->with(['client', 'chambre', 'sortiesChambre']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return StockTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStock::route('/'),
        ];
    }
}
