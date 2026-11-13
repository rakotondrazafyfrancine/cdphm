<?php

namespace App\Filament\Pages;

use App\Models\Journal;
use App\Models\Parametre;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Schema $form
 */
class Parametres extends Page
{
    protected static ?string $title = 'Paramètres';

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?int $navigationSort = 100;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'nom' => Parametre::lire('nom', 'CDPHM'),
            'logo' => Parametre::lire('logo'),
            'contact' => Parametre::lire('contact'),
            'lieu' => Parametre::lire('lieu'),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])->schema([
                    // à gauche : le logo et le résumé de l'établissement
                    Section::make()
                        ->extraAttributes(['class' => 'cd-profil-section cd-logo-section'])
                        ->columnSpan(['lg' => 1])
                        ->schema([
                            FileUpload::make('logo')
                                ->hiddenLabel()->placeholder(new \Illuminate\Support\HtmlString('&nbsp;'))
                                ->image()
                                ->imageEditor()
                                ->imagePreviewHeight('140')
                                ->disk('public')
                                ->directory('logos')
                                ->visibility('public')
                                ->maxSize(2048)
                                ->helperText('Clique sur le logo pour le changer.'),
                            View::make('filament.pages.carte-parametres'),
                        ]),

                    // à droite : les réglages
                    Group::make([
                        Section::make('Identité')
                            ->description('Ces informations apparaissent dans l’en-tête et sur les documents.')
                            ->icon('lucide-building-2')
                            ->columns(2)
                            ->schema([
                                TextInput::make('nom')->label('Nom affiché')->required()->maxLength(60)->columnSpanFull(),
                                TextInput::make('contact')->label('Contact')->tel()->prefixIcon('lucide-phone')->maxLength(60),
                                TextInput::make('lieu')->label('Lieu')->prefixIcon('lucide-map-pin')->maxLength(120),
                            ]),

                        Section::make('Mot de passe')
                            ->description('Laisse ces champs vides pour garder le mot de passe actuel.')
                            ->icon('lucide-lock-keyhole')
                            ->columns(2)
                            ->schema([
                                TextInput::make('current_password')
                                    ->label('Mot de passe actuel')
                                    ->password()->revealable()
                                    ->autocomplete('current-password')
                                    ->required(fn (Get $get) => filled($get('password')))
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                                TextInput::make('password')
                                    ->label('Nouveau mot de passe')
                                    ->password()->revealable()
                                    ->autocomplete('new-password')
                                    ->minLength(6)
                                    ->same('password_confirmation')
                                    ->live(debounce: 500)
                                    ->dehydrated(false),
                                TextInput::make('password_confirmation')
                                    ->label('Confirmer le mot de passe')
                                    ->password()->revealable()
                                    ->autocomplete('new-password')
                                    ->required(fn (Get $get) => filled($get('password')))
                                    ->dehydrated(false),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->livewireSubmitHandler('save')
                ->footer([
                    Action::make('save')->label('Enregistrer')->submit('save')->keyBindings(['mod+s']),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $brut = $this->data;

        $nouveauMotDePasse = $brut['password'] ?? null;

        if (filled($nouveauMotDePasse)) {
            $user = auth()->user();

            if (! Hash::check((string) ($brut['current_password'] ?? ''), $user->getAuthPassword())) {
                throw ValidationException::withMessages(['data.current_password' => 'Le mot de passe actuel est incorrect.']);
            }
        }

        Parametre::ecrire('nom', $data['nom']);
        Parametre::ecrire('logo', $data['logo'] ?: null);
        Parametre::ecrire('contact', filled($data['contact'] ?? null) ? trim($data['contact']) : null);
        Parametre::ecrire('lieu', filled($data['lieu'] ?? null) ? trim($data['lieu']) : null);

        if (filled($nouveauMotDePasse)) {
            $user->update(['password' => $nouveauMotDePasse]);

            // garde la session ouverte avec le nouveau mot de passe
            if (request()->hasSession()) {
                request()->session()->put(['password_hash_' . Filament::getAuthGuard() => $user->fresh()->getAuthPassword()]);
            }

            Journal::enregistrer('modification', 'Mot de passe modifié par ' . $user->pseudo, ['modele' => 'User', 'modele_id' => $user->getKey()]);
        }

        Notification::make()
            ->title(filled($nouveauMotDePasse) ? 'Paramètres et mot de passe enregistrés' : 'Paramètres enregistrés')
            ->success()->send();

        $this->redirect(static::getUrl(), navigate: true);
    }
}
