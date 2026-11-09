<?php

namespace App\Filament\Resources\Journaux;

use App\Filament\Resources\Journaux\Pages\ListJournaux;
use App\Models\Journal;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static ?string $modelLabel = 'entrée du journal';

    protected static ?string $pluralModelLabel = 'journal d\'activité';

    protected static ?string $navigationLabel = 'Journal d\'activité';

    protected static ?string $slug = 'journal';

    protected static ?int $navigationSort = 99;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-scroll-text';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i:s'),
            TextEntry::make('user_pseudo')->label('Utilisateur')->placeholder('-'),
            TextEntry::make('action')->badge(),
            TextEntry::make('description'),
            TextEntry::make('ip')->label('Adresse IP')->placeholder('-'),
            KeyValueEntry::make('anciennes_valeurs')->label('Avant')->columnSpanFull()->visible(fn ($record) => filled($record->anciennes_valeurs)),
            KeyValueEntry::make('nouvelles_valeurs')->label('Après')->columnSpanFull()->visible(fn ($record) => filled($record->nouvelles_valeurs)),
        ]);
    }

    public static function table(Table $table): Table
    {
        $actions = [
            'creation' => 'Création',
            'modification' => 'Modification',
            'suppression' => 'Suppression',
            'connexion' => 'Connexion',
            'deconnexion' => 'Déconnexion',
            'echec_connexion' => 'Échec de connexion',
        ];

        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('user_pseudo')->label('Utilisateur')->searchable()->placeholder('-'),
                TextColumn::make('action')->badge()
                    ->formatStateUsing(fn (?string $state) => $actions[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'creation' => 'success',
                        'modification' => 'info',
                        'suppression', 'echec_connexion' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('modele')->label('Élément')->placeholder('-'),
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')->options($actions),
                SelectFilter::make('modele')->label('Élément')
                    ->options(fn () => Journal::query()->whereNotNull('modele')->distinct()->pluck('modele', 'modele')->all()),
                SelectFilter::make('user_id')->label('Utilisateur')
                    ->options(fn () => Journal::query()->whereNotNull('user_id')->distinct()->pluck('user_pseudo', 'user_id')->all()),
                Filter::make('periode')
                    ->schema([DatePicker::make('du')->label('Du'), DatePicker::make('au')->label('Au')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['du'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['au'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([ViewAction::make()->label('Détails')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournaux::route('/'),
        ];
    }
}
