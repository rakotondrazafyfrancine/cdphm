<?php

namespace App\Providers;

use App\Models\ChambreFroide;
use App\Models\Client;
use App\Models\Journal;
use App\Models\Lot;
use App\Models\Produit;
use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use App\Models\Tarif;
use App\Models\Tunnel;
use App\Observers\JournalObserver;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Modèles dont les changements sont tracés dans le journal.
     */
    private const MODELES_JOURNALISES = [
        Client::class, Produit::class, Tarif::class, Tunnel::class,
        ChambreFroide::class, Lot::class, SortieChambre::class, SortieTunnel::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // boutons des fenêtres (Créer, Annuler, Confirmer…) alignés à droite
        \Filament\Actions\Action::configureUsing(fn (\Filament\Actions\Action $action) => $action->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End));

        Carbon::setLocale('fr');

        foreach (self::MODELES_JOURNALISES as $modele) {
            $modele::observe(JournalObserver::class);
        }

        Event::listen(Login::class, fn (Login $e) => Journal::enregistrer(
            'connexion', "Connexion de {$e->user->pseudo}", ['user_id' => $e->user->getKey(), 'user_pseudo' => $e->user->pseudo]
        ));
        Event::listen(Logout::class, fn (Logout $e) => $e->user && Journal::enregistrer(
            'deconnexion', "Déconnexion de {$e->user->pseudo}", ['user_id' => $e->user->getKey(), 'user_pseudo' => $e->user->pseudo]
        ));
        Event::listen(Failed::class, fn (Failed $e) => Journal::enregistrer(
            'echec_connexion', 'Échec de connexion pour « ' . ($e->credentials['pseudo'] ?? '?') . ' »', ['user_pseudo' => $e->credentials['pseudo'] ?? null]
        ));

        $this->enregistrerIconesLucide();
        $this->enregistrerWidgetsRapports();
    }

    /**
     * Les widgets de la page Rapports ne sont pas découverts par le panneau :
     * on les déclare à Livewire pour que leurs mises à jour (chargement, filtres) fonctionnent.
     */
    private function enregistrerWidgetsRapports(): void
    {
        foreach ([
            \App\Filament\Rapports\Widgets\RapportStatsWidget::class,
            \App\Filament\Rapports\Widgets\RapportMouvementsChart::class,
            \App\Filament\Rapports\Widgets\RapportStatutChart::class,
            \App\Filament\Rapports\Widgets\RapportMouvementsTable::class,
        ] as $widget) {
            Livewire::component(
                'app.filament.rapports.widgets.' . \Illuminate\Support\Str::kebab(class_basename($widget)),
                $widget
            );
        }
    }

    /**
     * Remplace les icônes internes de Filament (Heroicons) par Lucide.
     */
    private function enregistrerIconesLucide(): void
    {
        FilamentIcon::register([
            'actions::action-group' => 'lucide-ellipsis-vertical',
            'actions::create-action.grouped' => 'lucide-plus',
            'actions::delete-action' => 'lucide-trash-2',
            'actions::delete-action.grouped' => 'lucide-trash-2',
            'actions::delete-action.modal' => 'lucide-trash-2',
            'actions::edit-action' => 'lucide-pencil',
            'actions::edit-action.grouped' => 'lucide-pencil',
            'actions::view-action' => 'lucide-eye',
            'actions::view-action.grouped' => 'lucide-eye',
            'actions::replicate-action' => 'lucide-copy',
            'actions::replicate-action.grouped' => 'lucide-copy',
            'actions::restore-action' => 'lucide-undo-2',
            'actions::restore-action.grouped' => 'lucide-undo-2',
            'actions::modal.confirmation' => 'lucide-triangle-alert',
            'forms::components.text-input.actions.copy' => 'lucide-copy',
            'forms::components.text-input.actions.hide-password' => 'lucide-eye-off',
            'forms::components.text-input.actions.show-password' => 'lucide-eye',
            'forms::components.key-value.actions.delete' => 'lucide-trash-2',
            'forms::components.key-value.actions.reorder' => 'lucide-grip-vertical',
            'forms::components.repeater.actions.delete' => 'lucide-trash-2',
            'forms::components.select.actions.create-option' => 'lucide-plus',
            'forms::components.select.actions.edit-option' => 'lucide-pencil',
            'forms::components.checkbox-list.search-field' => 'lucide-search',
            'forms::components.toggle-buttons.boolean.true' => 'lucide-circle-check',
            'forms::components.toggle-buttons.boolean.false' => 'lucide-circle-x',
            'infolists::components.icon-entry.true' => 'lucide-circle-check',
            'infolists::components.icon-entry.false' => 'lucide-circle-x',
            'notifications::notification.close-button' => 'lucide-x',
            'notifications::notification.danger' => 'lucide-circle-x',
            'notifications::notification.info' => 'lucide-info',
            'notifications::notification.success' => 'lucide-circle-check',
            'notifications::notification.warning' => 'lucide-triangle-alert',
            'panels::global-search.field' => 'lucide-search',
            'panels::pages.dashboard.actions.filter' => 'lucide-funnel',
            'panels::pages.dashboard.navigation-item' => 'lucide-layout-dashboard',
            'panels::sidebar.collapse-button' => 'lucide-chevron-left',
            'panels::sidebar.expand-button' => 'lucide-chevron-right',
            'panels::sidebar.group.collapse-button' => 'lucide-chevron-up',
            'panels::sidebar.open-database-notifications-button' => 'lucide-bell',
            'panels::topbar.close-sidebar-button' => 'lucide-x',
            'panels::topbar.open-sidebar-button' => 'lucide-menu',
            'panels::topbar.group.toggle-button' => 'lucide-chevron-down',
            'panels::topbar.open-database-notifications-button' => 'lucide-bell',
            'panels::theme-switcher.dark-button' => 'lucide-moon',
            'panels::theme-switcher.light-button' => 'lucide-sun',
            'panels::theme-switcher.system-button' => 'lucide-sun-moon',
            'panels::user-menu.logout-button' => 'lucide-log-out',
            'panels::user-menu.profile-item' => 'lucide-user-round',
            'panels::user-menu.toggle-button' => 'lucide-chevron-down',
            'panels::widgets.account.logout-button' => 'lucide-log-out',
            'schema::components.tabs.dropdown-trigger-button' => 'lucide-chevron-down',
            'schema::components.tabs.more-tabs-button' => 'lucide-ellipsis',
            'schema::components.wizard.completed-step' => 'lucide-check',
            'tables::actions.column-manager' => 'lucide-columns-3',
            'tables::actions.disable-reordering' => 'lucide-check',
            'tables::actions.enable-reordering' => 'lucide-arrow-up-down',
            'tables::actions.filter' => 'lucide-funnel',
            'tables::actions.group' => 'lucide-layers',
            'tables::actions.open-bulk-actions' => 'lucide-ellipsis-vertical',
            'tables::columns.collapse-button' => 'lucide-chevron-down',
            'tables::columns.icon-column.true' => 'lucide-circle-check',
            'tables::columns.icon-column.false' => 'lucide-circle-x',
            'tables::empty-state' => 'lucide-package-search',
            'tables::filters.remove-all-button' => 'lucide-funnel-x',
            'tables::grouping.collapse-button' => 'lucide-chevron-up',
            'tables::header-cell.sort-asc-button' => 'lucide-arrow-up',
            'tables::header-cell.sort-button' => 'lucide-arrow-up-down',
            'tables::header-cell.sort-desc-button' => 'lucide-arrow-down',
            'tables::reorder.handle' => 'lucide-grip-vertical',
            'tables::search-field' => 'lucide-search',
            'widgets::chart-widget.filter' => 'lucide-funnel',
        ]);
    }
}
