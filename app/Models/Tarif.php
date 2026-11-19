<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    /** Tarifs modifiables depuis l'interface : tunnel (congélation) et chambre (stock). */
    public const CHAMBRE_CRUSTACES = 'Chambre langouste/crabe';

    public const DESIGNATIONS_GEREES = ['Tunnel', 'Chambre', self::CHAMBRE_CRUSTACES];

    protected static function booted(): void
    {
        // Un nouveau montant s'applique tout de suite aux lots encore présents.
        static::updated(function (Tarif $tarif) {
            if (! $tarif->wasChanged('montant')) {
                return;
            }

            $montant = (float) $tarif->montant;

            if ($tarif->designation === 'Tunnel') {
                Lot::where('statut', 'en_congelation')->update([
                    'tarif_applique' => $montant,
                    'montant_tunnel' => \Illuminate\Support\Facades\DB::raw('poids_entree * ' . $montant),
                ]);
            } elseif ($tarif->designation === 'Chambre') {
                Lot::where('statut', 'en_stock')->get()
                    ->reject(fn (Lot $l) => Lot::estCrustace($l->espece))
                    ->each(fn (Lot $l) => $l->update(['tarif_applique' => $montant]));
            } elseif ($tarif->designation === self::CHAMBRE_CRUSTACES) {
                Lot::where('statut', 'en_stock')->get()
                    ->filter(fn (Lot $l) => Lot::estCrustace($l->espece))
                    ->each(fn (Lot $l) => $l->update(['tarif_applique' => $montant]));
            }
        });
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->designation) {
            'Tunnel' => 'Congélation',
            'Chambre' => 'Stock (autres produits)',
            self::CHAMBRE_CRUSTACES => 'Stock langouste et crabe',
            default => (string) $this->designation,
        };
    }

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
}
