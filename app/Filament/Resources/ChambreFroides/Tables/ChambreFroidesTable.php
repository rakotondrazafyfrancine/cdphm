<?php

namespace App\Filament\Resources\ChambreFroides\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChambreFroidesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nom')->label('Nom'),
            TextColumn::make('capacite_tonnes')->label('Capacité (t)')->numeric(2),
        ]);
    }
}
