<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

/**
 * Ajoute un bouton « Liste / Grille » aux pages de liste Filament.
 */
trait ChangeVueListeGrille
{
    #[Url(as: 'vue')]
    public string $vue = 'liste';

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        if ($this->vue !== 'grille') {
            return $table;
        }

        return $table
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->columns([Stack::make(array_values($table->getColumns()))->space(2)]);
    }

    public function basculerVue(): void
    {
        $this->vue = $this->vue === 'grille' ? 'liste' : 'grille';
        $this->resetTable();
    }

    protected function actionVue(): Action
    {
        return Action::make('vue')
            ->label(fn () => $this->vue === 'grille' ? 'Vue liste' : 'Vue grille')
            ->icon(fn () => $this->vue === 'grille' ? 'lucide-list' : 'lucide-layout-grid')
            ->color('gray')
            ->action(fn () => $this->basculerVue());
    }
}
