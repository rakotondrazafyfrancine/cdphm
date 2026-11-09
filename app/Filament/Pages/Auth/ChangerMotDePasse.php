<?php

namespace App\Filament\Pages\Auth;

use App\Models\Journal;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Exposed;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Page « Mot de passe oublié » adaptée à un usage local : les comptes n'ont pas d'e-mail,
 * on saisit donc le pseudo et le nouveau mot de passe directement.
 */
class ChangerMotDePasse extends RequestPasswordReset
{
    #[Unexposed]
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('pseudo')
                ->label('Pseudo')
                ->required()
                ->autocomplete('username')
                ->autofocus(),
            TextInput::make('password')
                ->label('Nouveau mot de passe')
                ->password()->revealable()
                ->required()
                ->minLength(6)
                ->same('password_confirmation')
                ->autocomplete('new-password'),
            TextInput::make('password_confirmation')
                ->label('Confirmer le mot de passe')
                ->password()->revealable()
                ->required()
                ->autocomplete('new-password'),
        ]);
    }

    #[Exposed]
    public function request(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();

        $user = User::where('pseudo', $data['pseudo'])->first();

        if (! $user) {
            Notification::make()->title('Ce pseudo n\'existe pas.')->danger()->send();

            return;
        }

        $user->update(['password' => $data['password']]);

        Journal::enregistrer(
            'modification',
            'Mot de passe changé depuis la page de connexion (' . $user->pseudo . ')',
            ['user_pseudo' => $user->pseudo, 'modele' => 'User', 'modele_id' => $user->getKey()]
        );

        Notification::make()->title('Mot de passe modifié')->body('Tu peux maintenant te connecter.')->success()->send();

        $this->redirect(filament()->getLoginUrl());
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Changer le mot de passe')
            ->submit('request');
    }

    #[Unexposed]
    public function getTitle(): string|Htmlable
    {
        return 'Changer le mot de passe';
    }

    #[Unexposed]
    public function getHeading(): string|Htmlable|null
    {
        return 'Changer le mot de passe';
    }
}
