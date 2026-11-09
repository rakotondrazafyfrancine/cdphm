<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('pseudo')
            ->label('Pseudo')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'pseudo' => $data['pseudo'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.pseudo' => 'Identifiants incorrects.',
        ]);
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Bienvenue';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Connecte-toi pour accéder à ton espace de gestion.';
    }
}
