<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            // « Mot de passe oublié ? » sur la page de connexion : local par défaut, à activer ailleurs avec ALLOW_PASSWORD_RESET_FROM_LOGIN=true
            ->passwordReset(requestAction: env('ALLOW_PASSWORD_RESET_FROM_LOGIN', app()->environment('local')) ? \App\Filament\Pages\Auth\ChangerMotDePasse::class : null)
            ->brandName(fn () => \App\Models\Parametre::lire('nom', 'CDPHM'))
            ->brandLogo(fn () => ($l = \App\Models\Parametre::lire('logo')) ? \Illuminate\Support\Facades\Storage::disk('public')->url($l) : view('filament.hooks.logo-marque'))
            ->brandLogoHeight('2.75rem')
            ->spa()
            // fichiers à télécharger et documents à imprimer : de vraies adresses, hors navigation interne
            ->spaUrlExceptions(fn () => [
                url('/rapports/*'), url('/archives/*'), url('/lots/*'), url('/factures/*'), url('/sorties/*'),
                url('/sorties-chambre/*'), url('/sorties-tunnel/*'), url('/tunnels/*/print'),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->defaultAvatarProvider(\App\Filament\AvatarInitiale::class)
            ->userMenuItems([
                'profile' => \Filament\Actions\Action::make('profile')
                    ->label(fn () => new \Illuminate\Support\HtmlString(
                        '<span class="cd-pc"><span class="cd-pc-avatar">' . e(mb_strtoupper(mb_substr((string) auth()->user()->pseudo, 0, 1))) . '</span>'
                        . '<span class="cd-pc-textes"><b>' . e(auth()->user()->pseudo) . '</b></span></span>'
                    )),
                \Filament\Actions\Action::make('journal')
                    ->label("Journal d'activité")
                    ->icon('lucide-scroll-text')
                    ->url(fn () => \App\Filament\Resources\Journaux\JournalResource::getUrl('index')),
                \Filament\Actions\Action::make('parametres')
                    ->label('Paramètres')
                    ->icon('lucide-settings')
                    ->url(fn () => \App\Filament\Pages\Parametres::getUrl()),
                'logout' => fn (\Filament\Actions\Action $action) => $action->label('Déconnexion')->icon('lucide-log-out')->color('danger'),
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Opérations')->collapsible(),
                \Filament\Navigation\NavigationGroup::make('Infrastructures')->collapsible()->collapsed(),
                \Filament\Navigation\NavigationGroup::make('Données de base')->collapsible()->collapsed(),
                \Filament\Navigation\NavigationGroup::make('Suivi')->collapsible()->collapsed(),
                \Filament\Navigation\NavigationGroup::make('Administration')->collapsible()->collapsed(),
            ])
            ->font('Inter', url: '/fonts/inter/inter.css', provider: \Filament\FontProviders\LocalFontProvider::class)
            ->maxContentWidth('full')
            ->sidebarWidth('15rem')
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.hooks.entete-info'))
            ->renderHook(PanelsRenderHook::PAGE_START, fn () => view('filament.hooks.flash'))
            ->renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, fn () => view('filament.hooks.login-marque'))
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.hooks.session-expiree'))
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.hooks.squelette-page'))
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<style>' . file_get_contents(resource_path('css/admin-theme.css')) . '</style>'
            )
            ->colors([
                'primary' => Color::hex('#336399'),
                'sky' => Color::Blue,
                'teal' => Color::Emerald,
                'indigo' => Color::Rose,
                'cyan' => Color::Cyan,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsLots::class,
                \App\Filament\Widgets\EntreesParMois::class,
                \App\Filament\Widgets\RepartitionStock::class,
                \App\Filament\Widgets\RevenusParMois::class,
                \App\Filament\Widgets\LotsParEspece::class,
                \App\Filament\Widgets\TopClients::class,
                \App\Filament\Widgets\DerniersLots::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\VerifierAlertesTunnel::class,
            ]);
    }
}
