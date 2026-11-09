<?php

namespace App\Filament\Resources\Tarifs;

use App\Filament\Resources\Tarifs\Pages\ListTarifs;
use App\Filament\Resources\Tarifs\Schemas\TarifForm;
use App\Filament\Resources\Tarifs\Tables\TarifsTable;
use App\Models\Tarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TarifResource extends Resource
{
    protected static ?string $model = Tarif::class;

    protected static ?string $modelLabel = 'tarif';

    protected static ?string $pluralModelLabel = 'tarifs';

    protected static ?string $navigationLabel = 'Tarifs';

    protected static ?int $navigationSort = 7;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-hand-coins';

    /**
     * Seuls deux tarifs sont gérés : la congélation (tunnel) et le stock (chambre).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('designation', Tarif::DESIGNATIONS_GEREES);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return TarifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TarifsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTarifs::route('/'),
        ];
    }
}
