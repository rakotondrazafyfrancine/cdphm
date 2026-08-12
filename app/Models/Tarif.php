<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'categorie',    // congelation, transport, transport_ville, location
        'type',         // avec_go, sans_go, null
        'designation',  // Nom du tarif
        'montant',      // Valeur en Ar
        'details'       // JSON pour infos supplémentaires
    ];

    protected $casts = [
        'details' => 'array',
        'montant' => 'decimal:2'
    ];

    // Scopes
    public function scopeCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Récupérer le tarif de transport pour une destination
    public static function getTransportTarif(string $destination, bool $avecGO): ?self
    {
        $type = $avecGO ? 'avec_go' : 'sans_go';

        return self::where('categorie', 'transport')
            ->where('type', $type)
            ->where('designation', 'LIKE', "%{$destination}%")
            ->first();
    }

    // Récupérer le tarif de transport en ville
    public static function getVilleTarif(string $typeCamion, float $distance): ?self
    {
        return self::where('categorie', 'transport_ville')
            ->where('details->type', $typeCamion)
            ->where('details->distance_min', '<=', $distance)
            ->where('details->distance_max', '>=', $distance)
            ->first();
    }

    // Exception spéciale : Camion 4T Mahajanga-Tananarive
    public static function getTarifSpecial4T(string $destination, float $poids): ?float
    {
        if ($destination === 'Mahajanga - Tamanarivo' && $poids <= 4000) {
            return 350; // Tarif exceptionnel 350 Ar/Kg
        }
        return null;
    }
}
