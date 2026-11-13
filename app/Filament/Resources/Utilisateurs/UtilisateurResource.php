<?php

namespace App\Filament\Resources\Utilisateurs;

use App\Filament\Resources\Utilisateurs\Pages\GererUtilisateurs;
use App\Models\User;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UtilisateurResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-users';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('photo')
                ->label('Photo de profil')
                ->image()->avatar()->imageEditor()->circleCropper()
                ->disk('public')->directory('photos')->visibility('public')
                ->maxSize(2048)->columnSpanFull(),
            TextInput::make('nom')->label('Nom')->required()->maxLength(255),
            TextInput::make('prenom')->label('Prénom')->required()->maxLength(255),
            TextInput::make('matricule')->label('N° IM')->maxLength(30),
            TextInput::make('departement')->label('Département')->maxLength(255),
            TextInput::make('fonction')->label('Fonction')->maxLength(255),
            Select::make('role')->label('Rôle')->options(User::ROLES)->required(),
            TextInput::make('pseudo')->label('Pseudo (identifiant de connexion)')
                ->required()->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('password')->label('Mot de passe')
                ->password()->revealable()->minLength(6)
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Laisse vide pour ne pas le changer.' : null),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')->label('Nom et prénom')
                    ->state(fn (User $r) => $r->nomComplet())
                    ->searchable(['nom', 'prenom', 'pseudo']),
                TextColumn::make('pseudo')->label('Pseudo')->searchable(),
                TextColumn::make('role')->label('Rôle')->state(fn (User $r) => $r->libelleRole())->badge(),
                TextColumn::make('matricule')->label('N° IM')->placeholder('—'),
                TextColumn::make('departement')->label('Département')->placeholder('—'),
                TextColumn::make('fonction')->label('Fonction')->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()->label('')->tooltip('Modifier'),
                DeleteAction::make()->label('')->tooltip('Supprimer')
                    ->visible(fn (User $r) => $r->id !== auth()->id()),
            ])
            ->headerActions([
                CreateAction::make()->label('Nouvel utilisateur')->icon('lucide-plus'),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => GererUtilisateurs::route('/'),
        ];
    }
}
