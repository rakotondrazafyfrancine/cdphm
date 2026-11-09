<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotResource;
use App\Filament\Resources\Lots\Schemas\LotForm;
use App\Models\Client;
use App\Models\ClientProduit;
use App\Models\Lot;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLot extends CreateRecord
{
    protected static string $resource = LotResource::class;

    protected static ?string $title = 'Nouvelle entrée';

    protected static bool $canCreateAnother = false;

    /** @var Collection<int, Lot> */
    protected Collection $lotsCrees;

    public function form(Schema $schema): Schema
    {
        return LotForm::configureEntree($schema);
    }

    /** Un lot est créé pour chaque produit saisi. */
    protected function handleRecordCreation(array $data): Model
    {
        $client = Client::findOrFail($data['client_id']);

        // une même référence relie les lots d'une même livraison : un seul bon pour tous
        $reference = 'E' . strtoupper(\Illuminate\Support\Str::random(8));

        // premier produit (bloc principal) puis les produits ajoutés
        $lignes = collect([[
            'espece' => $data['espece'] ?? null,
            'poids_entree' => $data['poids_entree'] ?? null,
            'tunnel_id' => $data['tunnel_id'] ?? null,
            'chambre_id' => $data['chambre_id'] ?? null,
        ]])->merge(array_values($data['lignes'] ?? []));

        $this->lotsCrees = DB::transaction(function () use ($client, $lignes, $reference) {
            return $lignes->map(fn (array $ligne) => $this->creerLot($client, $ligne, $reference));
        });

        return $this->lotsCrees->first();
    }

    private function creerLot(Client $client, array $ligne, string $reference): Lot
    {
        $espece = LotForm::nomProduit($ligne);

        $lot = new Lot();
        $lot->client_id = $client->id;
        $lot->entree_ref = $reference;
        $lot->espece = $espece;
        $lot->contact = $client->contact;
        $lot->poids_entree = $ligne['poids_entree'];
        $lot->tunnel_id = $ligne['tunnel_id'] ?? null;
        $lot->chambre_id = $ligne['chambre_id'] ?? null;
        $lot->statut = $lot->chambre_id ? 'en_stock' : 'en_congelation';

        if ($lot->tunnel_id) {
            $lot->tarif_applique = Lot::tarifTunnel();
            $lot->montant_tunnel = $lot->poids_entree * $lot->tarif_applique;
        }

        if ($lot->chambre_id) {
            $lot->date_entree_chambre = now();
            $lot->poids_restant = $lot->poids_entree;
            $lot->tarif_applique = Lot::tarifChambre();
        }

        $lot->save();

        // Le produit est ajouté à la fiche du client s'il n'y est pas déjà
        ClientProduit::firstOrCreate(['client_id' => $client->id, 'nom' => $espece]);

        return $lot;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        $numeros = $this->lotsCrees->pluck('numero');

        return $numeros->count() > 1
            ? $numeros->count() . ' lots enregistrés : ' . $numeros->implode(', ')
            : 'Lot ' . $numeros->first() . ' enregistré avec succès';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('ajouterProduit')
                ->label('Ajouter un produit')
                ->icon('lucide-plus')
                ->color('gray')
                ->outlined()
                ->action(function () {
                    $lignes = $this->data['lignes'] ?? [];
                    $lignes[(string) Str::uuid()] = [
                        'espece' => null,
                        'poids_entree' => null,
                        'tunnel_id' => request()->query('tunnel_id') ?: null,
                        'chambre_id' => request()->query('chambre_id') ?: null,
                    ];
                    $this->data['lignes'] = $lignes;
                }),
            $this->getCreateFormAction()->label('Enregistrer')->icon('lucide-check'),
            $this->getCancelFormAction()->label('Annuler'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return LotResource::getUrl('index');
    }
}
