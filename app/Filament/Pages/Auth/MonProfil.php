<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Contracts\Support\Htmlable;

/** « Mon profil » : la fiche de renseignements de l'agent connecté et son mot de passe. */
class MonProfil extends EditProfile
{
    #[Unexposed]
    public function getTitle(): string|Htmlable
    {
        return 'Mon profil';
    }

    #[Unexposed]
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Section::make()
                    ->extraAttributes(['class' => 'cd-profil-section'])
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        FileUpload::make('photo')
                            ->hiddenLabel()->placeholder(new \Illuminate\Support\HtmlString('&nbsp;'))
                            ->image()->avatar()->imageEditor()->circleCropper()
                            ->disk('public')->directory('photos')->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Clique sur la photo pour la changer.'),
                        View::make('filament.pages.carte-profil'),
                    ]),
                Group::make([
                    Section::make('Renseignements')
                                                ->columns(2)
                        ->schema([
                            TextInput::make('nom')->label('Nom')->required()->maxLength(255),
                            TextInput::make('prenom')->label('Prénom')->required()->maxLength(255),
                            TextInput::make('matricule')->label('N° IM')->maxLength(30),
                            TextInput::make('departement')->label('Département')->maxLength(255),
                            TextInput::make('fonction')->label('Fonction')->maxLength(255)->columnSpanFull(),
                        ]),
                    Section::make('Compte')
                        ->columns(2)
                        ->schema([
                            TextInput::make('pseudo')->label('Pseudo (identifiant de connexion)')->disabled()->dehydrated(false),
                            TextInput::make('role_libelle')->label('Rôle')->disabled()->dehydrated(false)
                                ->formatStateUsing(fn () => $this->getUser()->libelleRole()),
                            TextInput::make('password')
                                ->label('Nouveau mot de passe')
                                ->password()->revealable()
                                ->minLength(6)
                                ->rule('confirmed')
                                ->autocomplete('new-password')
                                ->helperText('Laisse vide pour garder le mot de passe actuel.')
                                ->dehydrated(fn ($state) => filled($state)),
                            TextInput::make('password_confirmation')
                                ->label('Confirmer le mot de passe')
                                ->password()->revealable()
                                ->requiredWith('password')
                                ->dehydrated(false),
                        ]),
                ])->columnSpan(['lg' => 2]),
            ]),
        ]);
    }
}
