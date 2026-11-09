<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Un mouvement = une entrée de lot, ou une sortie (chambre ou tunnel).
 * Il n'a pas de table : la liste est construite par une requête qui réunit les lots et les sorties.
 */
class Mouvement extends Model
{
    protected $table = 'mouvements';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = ['date_mvt' => 'datetime'];

    public function getNumeroAttribute(): string
    {
        return 'LOT-' . str_pad((string) $this->lot_id, 4, '0', STR_PAD_LEFT);
    }

    /** @param  Builder|\Illuminate\Database\Eloquent\Builder  $lotsFiltres  lots retenus par les filtres de la page */
    public static function requete($lotsFiltres): \Illuminate\Database\Eloquent\Builder
    {
        $ids = $lotsFiltres->clone()->reorder()->select('lots.id');

        $entrees = DB::table('lots')
            ->join('clients', 'clients.id', '=', 'lots.client_id')
            ->leftJoin('tunnels', 'tunnels.id', '=', 'lots.tunnel_id')
            ->leftJoin('chambre_froides', 'chambre_froides.id', '=', 'lots.chambre_id')
            ->whereIn('lots.id', $ids)
            ->selectRaw("CONCAT('E', lots.id) as id, lots.id as lot_id, 'Entrée' as type, clients.nom as client_nom, lots.espece as produit,
                lots.poids_entree as poids, NULL as montant, lots.statut as statut, lots.created_at as date_mvt,
                COALESCE(chambre_froides.nom, tunnels.nom) as emplacement, NULL as reste");

        $chambre = DB::table('sorties_chambre')
            ->join('lots', 'lots.id', '=', 'sorties_chambre.lot_id')
            ->join('clients', 'clients.id', '=', 'lots.client_id')
            ->whereIn('lots.id', $ids)
            ->selectRaw("CONCAT('C', sorties_chambre.id) as id, lots.id as lot_id,
                CASE WHEN sorties_chambre.reste_apres IS NULL THEN 'Sortie chambre'
                     WHEN sorties_chambre.reste_apres > 0 THEN 'Sortie partielle (chambre)'
                     ELSE 'Sortie totale (chambre)' END as type,
                clients.nom as client_nom, lots.espece as produit, sorties_chambre.poids_sorti as poids, sorties_chambre.montant as montant,
                sorties_chambre.statut as statut, sorties_chambre.created_at as date_mvt, sorties_chambre.emplacement as emplacement,
                sorties_chambre.reste_apres as reste");

        $tunnel = DB::table('sorties_tunnel')
            ->join('lots', 'lots.id', '=', 'sorties_tunnel.lot_id')
            ->join('clients', 'clients.id', '=', 'lots.client_id')
            ->whereIn('lots.id', $ids)
            ->selectRaw("CONCAT('T', sorties_tunnel.id) as id, lots.id as lot_id, 'Sortie tunnel' as type,
                clients.nom as client_nom, lots.espece as produit, sorties_tunnel.poids_sorti as poids, sorties_tunnel.montant as montant,
                sorties_tunnel.statut as statut, sorties_tunnel.created_at as date_mvt, sorties_tunnel.emplacement as emplacement,
                NULL as reste");

        return static::query()->fromSub($entrees->unionAll($chambre)->unionAll($tunnel), 'mouvements');
    }
}
