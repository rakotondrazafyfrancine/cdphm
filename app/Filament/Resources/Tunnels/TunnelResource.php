<?php

namespace App\Filament\Resources\Tunnels;

use App\Filament\Resources\Tunnels\Pages\CreateTunnel;
use App\Filament\Resources\Tunnels\Pages\EditTunnel;
use App\Filament\Resources\Tunnels\Pages\ViewTunnel;
use App\Filament\Resources\Tunnels\RelationManagers\LotsRelationManager;
use App\Filament\Resources\Tunnels\Pages\ListTunnels;
use App\Filament\Resources\Tunnels\Schemas\TunnelForm;
use App\Filament\Resources\Tunnels\Tables\TunnelsTable;
use App\Models\Tunnel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TunnelResource extends Resource
{
    protected static ?string $model = Tunnel::class;

    protected static ?string $modelLabel = 'tunnel';

    protected static ?string $pluralModelLabel = 'tunnels';

    protected static ?string $navigationLabel = 'Tunnels';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'Infrastructures';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-snowflake';

    public static function form(Schema $schema): Schema
    {
        return TunnelForm::configure($schema);
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
        return TunnelsTable::configure($table);
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
            'index' => ListTunnels::route('/'),
            'create' => CreateTunnel::route('/create'),
            'view' => ViewTunnel::route('/{record}'),
            'edit' => EditTunnel::route('/{record}/edit'),
        ];
    }
}
