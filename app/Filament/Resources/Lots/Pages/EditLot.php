<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotResource;
use App\Filament\Resources\Lots\Schemas\LotForm;
use App\Models\Client;
use App\Models\ClientProduit;
use App\Models\Lot;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLot extends EditRecord
{
    protected static string $resource = LotResource::class;

    protected static ?string $title = "Modifier l'entrée";

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $client = Client::findOrFail($data['client_id']);
        $ancienPoids = (float) $record->poids_entree;
        $nouveauPoids = (float) $data['poids_entree'];

        $record->client_id = $client->id;
        $record->contact = $client->contact;
        $record->espece = LotForm::nomProduit($data);
        $record->poids_entree = $nouveauPoids;
        $record->tunnel_id = $data['tunnel_id'] ?? null;
        $record->chambre_id = $data['chambre_id'] ?? null;
        $record->statut = $record->chambre_id ? 'en_stock' : 'en_congelation';

        if ($record->tunnel_id) {
            $record->tarif_applique = Lot::tarifTunnel();
            $record->montant_tunnel = $nouveauPoids * $record->tarif_applique;
        }

        if ($record->chambre_id) {
            $record->date_entree_chambre ??= now();
            $record->tarif_applique = Lot::tarifChambre();
            // Les sorties partielles déjà faites sont conservées : on ajuste le reste par l'écart de poids.
            $restant = $record->poids_restant ?? $ancienPoids;
            $record->poids_restant = max(0, $restant + ($nouveauPoids - $ancienPoids));
        }

        $record->save();

        ClientProduit::firstOrCreate(['client_id' => $client->id, 'nom' => $record->espece]);

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Lot ' . $this->record->numero . ' modifié';
    }

    protected function getRedirectUrl(): string
    {
        return LotResource::getUrl('index');
    }
}
