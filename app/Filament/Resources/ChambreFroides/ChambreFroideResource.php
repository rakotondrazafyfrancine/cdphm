<?php

namespace App\Filament\Resources\ChambreFroides;

use App\Filament\Resources\ChambreFroides\Pages\CreateChambreFroide;
use App\Filament\Resources\ChambreFroides\Pages\EditChambreFroide;
use App\Filament\Resources\ChambreFroides\Pages\ViewChambreFroide;
use App\Filament\Resources\ChambreFroides\RelationManagers\LotsRelationManager;
use App\Filament\Resources\ChambreFroides\Pages\ListChambreFroides;
use App\Filament\Resources\ChambreFroides\Schemas\ChambreFroideForm;
use App\Filament\Resources\ChambreFroides\Tables\ChambreFroidesTable;
use App\Models\ChambreFroide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ChambreFroideResource extends Resource
{
    protected static ?string $model = ChambreFroide::class;

    protected static ?string $modelLabel = 'chambre froide';

    protected static ?string $pluralModelLabel = 'chambres froides';

    protected static ?string $navigationLabel = 'Chambres';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'Infrastructures';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-thermometer-snowflake';

    public static function form(Schema $schema): Schema
    {
        return ChambreFroideForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.infolists.stats-infrastructure')
                ->viewData(fn ($record) => ['record' => $record])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ChambreFroidesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChambreFroides::route('/'),
            'create' => CreateChambreFroide::route('/create'),
            'view' => ViewChambreFroide::route('/{record}'),
            'edit' => EditChambreFroide::route('/{record}/edit'),
        ];
    }
}
