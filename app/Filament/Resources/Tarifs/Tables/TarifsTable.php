<?php

namespace App\Filament\Resources\Tarifs\Tables;

use App\Filament\Resources\Tarifs\Schemas\TarifForm;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TarifsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->defaultSort('id')
            ->columns([
                TextColumn::make('libelle')->label('Désignation')->weight('bold')
                    ->icon(fn ($record) => $record->designation === 'Tunnel' ? 'lucide-snowflake' : 'lucide-warehouse'),
                TextColumn::make('montant')->label('Tarif')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' Ar')
                    ->description('par kg et par jour')
                    ->badge()->color('primary'),
                TextColumn::make('updated_at')->label('Dernière modification')->since(),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('lucide-pencil')
                    ->modalHeading(fn ($record) => 'Modifier le tarif : ' . $record->libelle)
                    ->modalWidth('md')
                    ->schema(TarifForm::components())
                    ->fillForm(fn ($record) => ['montant' => $record->montant])
                    ->successNotificationTitle('Tarif modifié et appliqué'),
            ]);
    }
}
