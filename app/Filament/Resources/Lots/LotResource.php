<?php

namespace App\Filament\Resources\Lots;

use App\Filament\Resources\Lots\Pages\CreateLot;
use App\Filament\Resources\Lots\Pages\EditLot;
use App\Filament\Resources\Lots\Pages\ListLots;
use App\Filament\Resources\Lots\Schemas\LotForm;
use App\Filament\Resources\Lots\Tables\LotsTable;
use App\Models\Lot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LotResource extends Resource
{
    protected static ?string $model = Lot::class;

    protected static ?string $modelLabel = 'lot';

    protected static ?string $navigationLabel = 'Entrées/Congélation';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Opérations';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-truck';

    public static function form(Schema $schema): Schema
    {
        return LotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LotsTable::configure($table)->poll(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLots::route('/'),
            'create' => CreateLot::route('/create'),
            'edit' => EditLot::route('/{record}/edit'),
        ];
    }
}
