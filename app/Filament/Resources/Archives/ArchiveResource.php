<?php

namespace App\Filament\Resources\Archives;

use App\Filament\Resources\Archives\Pages\ListArchives;
use App\Models\Archive;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArchiveResource extends Resource
{
    protected static ?string $model = Archive::class;

    protected static ?string $modelLabel = 'archive';

    protected static ?string $pluralModelLabel = 'archives';

    protected static ?string $navigationLabel = 'Archives';

    protected static ?int $navigationSort = 11;

    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-archive';

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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Rechercher un client, un lot, un numéro...')
            ->emptyStateHeading('Aucun document archivé pour le moment.')
            ->emptyStateDescription('Chaque bon d\'entrée, bon de sortie et facture est archivé automatiquement à sa première émission.')
            ->emptyStateIcon('lucide-archive')
            ->columns([
                TextColumn::make('numero')->label('N°')->weight('bold')->color('primary')->searchable()->sortable(),
                TextColumn::make('type')->label('Document')->badge()
                    ->formatStateUsing(fn (string $state) => Archive::TYPES[$state][0] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'bon_entree' => 'primary', 'bon_sortie' => 'warning', default => 'success' }),
                TextColumn::make('client_nom')->label('Client')->searchable()->sortable(),
                TextColumn::make('resume')->label('Lots')->searchable()->wrap()->limit(60),
                TextColumn::make('poids_total')->label('Poids (kg)')->numeric(2)->sortable(),
                TextColumn::make('montant_total')->label('Montant (Ar)')->numeric(0)->placeholder('-'),
                TextColumn::make('created_at')->label('Émis le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('emis_par')->label('Par')->placeholder('-'),
                TextColumn::make('impressions')->label('Impressions')->alignCenter()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->label('Document')
                    ->options(collect(Archive::TYPES)->map(fn ($t) => $t[0])->all()),
                SelectFilter::make('client_id')->label('Client')
                    ->options(fn () => Archive::query()->orderBy('client_nom')->pluck('client_nom', 'client_id')->all())
                    ->searchable(),
                Filter::make('periode')
                    ->schema([DatePicker::make('du')->label('Du'), DatePicker::make('au')->label('Au')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['du'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['au'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActionsAlignment('center')
            ->recordActions([
                Action::make('ouvrir')
                    ->label('Ouvrir le document')
                    ->icon('lucide-file-text')
                    ->iconButton()
                    ->tooltip('Ouvrir / imprimer le document archivé')
                    ->color('primary')
                    ->url(fn (Archive $record) => route('archives.voir', $record->id), shouldOpenInNewTab: true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArchives::route('/'),
        ];
    }
}
