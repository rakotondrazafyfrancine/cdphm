<?php

namespace App\Filament\Pages;

use App\Filament\Rapports\Widgets\RapportMouvementsChart;
use App\Filament\Rapports\Widgets\RapportMouvementsTable;
use App\Filament\Rapports\Widgets\RapportStatsWidget;
use App\Filament\Rapports\Widgets\RapportStatutChart;
use App\Models\ChambreFroide;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Tunnel;
use App\Support\RapportFiltres;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Rapports extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'rapports';

    protected static ?string $title = 'Rapports';

    protected static ?string $navigationLabel = 'Rapports';

    protected static ?int $navigationSort = 10;

    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-chart-column';

    public function getSubheading(): ?string
    {
        return 'Suivi des entrées, des sorties et du stock';
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 3];
    }

    public function getWidgets(): array
    {
        return [
            RapportStatsWidget::class,
            RapportMouvementsChart::class,
            RapportStatutChart::class,
            RapportMouvementsTable::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Filtres')
                ->columnSpanFull()
                ->icon('lucide-funnel')
                ->collapsible()
                ->columns(['default' => 1, 'md' => 3, 'xl' => 4])
                ->schema([
                    DatePicker::make('date_debut')->label('Entrée à partir du')->prefixIcon('lucide-calendar')->native(false)->displayFormat('d/m/Y'),
                    DatePicker::make('date_fin')->label('Entrée jusqu\'au')->prefixIcon('lucide-calendar')->native(false)->displayFormat('d/m/Y'),
                    Select::make('mouvement')->label('Mouvement')->prefixIcon('lucide-clipboard-list')->native(false)
                        ->options(['entree' => 'En stock', 'sortie' => 'Sorties'])->placeholder('Tous'),
                    Select::make('espece')->label('Produit')->prefixIcon('lucide-tag')->native(false)->searchable()
                        ->options(fn () => Lot::whereNotNull('espece')->distinct()->orderBy('espece')->pluck('espece', 'espece')->all())->placeholder('Tous'),
                    Select::make('client_id')->label('Client')->prefixIcon('lucide-user-round')->native(false)->searchable()
                        ->options(fn () => Client::orderBy('nom')->pluck('nom', 'id')->all())->placeholder('Tous'),
                    Select::make('tunnel_id')->label('Tunnel')->prefixIcon('lucide-snowflake')->native(false)
                        ->options(fn () => Tunnel::orderBy('nom')->pluck('nom', 'id')->all())->placeholder('Tous'),
                    Select::make('chambre_id')->label('Chambre')->prefixIcon('lucide-thermometer-snowflake')->native(false)
                        ->options(fn () => ChambreFroide::orderBy('nom')->pluck('nom', 'id')->all())->placeholder('Toutes'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reinitialiser')
                ->label('Réinitialiser')
                ->icon('lucide-rotate-ccw')
                ->color('gray')
                ->visible(fn () => filled(RapportFiltres::parametres($this->filters)))
                ->action(function () {
                    $this->filters = [];
                    $this->filtersForm->fill();
                }),
            Action::make('imprimer')
                ->label('Imprimer')
                ->icon('lucide-printer')
                ->color('gray')
                ->url(fn () => route('rapports.imprimer', RapportFiltres::parametres($this->filters)), shouldOpenInNewTab: true),
            Action::make('excel')
                ->label('Exporter Excel')
                ->icon('lucide-file-spreadsheet')
                ->url(fn () => route('rapports.export-excel', RapportFiltres::parametres($this->filters))),
        ];
    }
}
