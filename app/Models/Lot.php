<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lot extends Model
{
    protected $table = 'lots';

    protected $fillable = [
        // Informations générales
        'client_id',
        'entree_ref',
        'espece',
        'contact',

        // Poids
        'poids_entree',
        'poids_sortie',

        // Infrastructure
        'tunnel_id',
        'chambre_id',

        // Gestion du temps
        'heures_tunnel',
        'duree_indeterminee',


        // Frais

        // Paiements
        'paye_tunnel',
        'paye_chambre',
        'montant_tunnel',

        // Dates
        'date_entree_chambre',
        'date_sortie',

        // Autres
        'statut',
    ];

    protected $casts = [
        'poids_entree' => 'decimal:2',
        'poids_sortie' => 'decimal:2',
        'montant_tunnel' => 'decimal:2',
        'heures_tunnel' => 'float',
        'paye_tunnel' => 'boolean',
        'paye_chambre' => 'boolean',
        'duree_indeterminee' => 'boolean',
        'date_entree_chambre' => 'datetime',
        'date_sortie' => 'datetime',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Relation avec le client
     */
    public static function tarifTunnel(): float
    {
        return (float) (Tarif::where('designation', 'Tunnel')->value('montant') ?? 450);
    }

    public static function tarifChambre(): float
    {
        return (float) (Tarif::where('designation', 'Chambre')->value('montant') ?? 25);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec le tunnel
     */
    public function tunnel(): BelongsTo
    {
        return $this->belongsTo(Tunnel::class);
    }

    /**
     * Relation avec la chambre froide
     * Utilise le modèle ChambreFroide avec la clé chambre_froide_id
     */
    public function chambre(): BelongsTo
    {
        return $this->belongsTo(ChambreFroide::class, 'chambre_id');
    }

    /**
     * Relation avec les missions de transport
     */

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Obtenir le temps en tunnel en jours
     */
    public function getTempsTunnelEnJoursAttribute(): float
    {
        if ($this->heures_tunnel) {
            return $this->heures_tunnel / 24;
        }
        return 1;
    }

    /**
     * Obtenir le temps en chambre en jours
     */
    public function getTempsChambreEnJoursAttribute(): float
    {
        if ($this->duree_indeterminee && $this->date_entree_chambre) {
            return (int) $this->date_entree_chambre->diffInDays(now()) + 1;
        }
        return 1;
    }

    /**
     * Vérifier si le lot est en stock
     */
    public function getEstEnStockAttribute(): bool
    {
        return $this->statut === 'en_stock';
    }

    /**
     * Vérifier si le lot est payé
     */
    public function getEstPayeAttribute(): bool
    {
        if ($this->type_infrastructure === 'tunnel') {
            return $this->paye_tunnel;
        }
        return $this->paye_chambre;
    }

    /**
     * Obtenir le nom du client
     */
    public function getNomClientAttribute(): string
    {
        return $this->client ? $this->client->nom : 'Client inconnu';
    }

    /**
     * Obtenir le nom de l'infrastructure
     */
    public function getNomInfrastructureAttribute(): string
    {
        if ($this->type_infrastructure === 'tunnel' && $this->tunnel) {
            return $this->tunnel->nom;
        }
        if ($this->type_infrastructure === 'chambre' && $this->chambre) {
            return $this->chambre->nom;
        }
        return 'Aucune';
    }

    /**
     * Obtenir la durée de stockage formatée
     */
    public function getDureeStockageFormateeAttribute(): string
    {
        if ($this->type_infrastructure === 'tunnel') {
            $heures = $this->heures_tunnel ?? 24;
            return $heures . 'h (' . round($heures / 24, 2) . ' jours)';
        }

        if ($this->duree_indeterminee) {
            return 'Durée libre (facturation à la sortie)';
        }

        $jours = 1;
        return $jours . ' jour(s)';
    }

    /**
     * Obtenir le poids final (sortie)
     */
    public function getPoidsFinalAttribute(): float
    {
        return $this->poids_sortie ?? $this->poids_entree;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope pour les lots en stock
     */
    public function scopeEnStock($query)
    {
        return $query->where('statut', 'en_stock');
    }

    /**
     * Scope pour les lots en tunnel
     */
    public function scopeEnTunnel($query)
    {
        return $query->where('statut', 'en_congelation');
    }

    /**
     * Scope pour les lots en chambre
     */
    public function scopeEnChambre($query)
    {
        return $query->where('statut', 'en_stock');
    }

    /**
     * Scope pour les lots non payés
     */
    public function scopeNonPayes($query)
    {
        return $query->where(function ($q) {
            $q->where('paye_tunnel', false)
                ->orWhere('paye_chambre', false);
        })->where('statut', 'en_stock');
    }

    /**
     * Scope pour les lots avec dépassement tunnel (>24h)
     */
    public function scopeDepassementTunnel($query)
    {
        return $query->where('statut', 'en_congelation')
            ->where('heures_tunnel', '>', 24);
    }

    /**
     * Scope par client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope par période
     */
    public function scopeEntreDates($query, $debut, $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }

    /**
     * Scope pour les lots d'une espèce donnée
     */
    public function scopeEspece($query, $espece)
    {
        return $query->where('espece', 'LIKE', '%' . $espece . '%');
    }

    // ==========================================
    // MÉTHODES UTILITAIRES
    // ==========================================

    /**
     * Vérifier si le lot a dépassé 24h en tunnel
     */
    public function hasDepassementTunnel(): bool
    {
        return $this->type_infrastructure === 'tunnel'
            && ($this->heures_tunnel ?? 0) > 24;
    }

    /**
     * Calculer la pénalité de dépassement (50% majoré)
     * Tarif tunnel par défaut : 450 Ar/kg/jour
     */
    public function calculerPenaliteTunnel(float $tarif = 450): float
    {
        if (!$this->hasDepassementTunnel()) {
            return 0;
        }

        $heuresExcedentaires = ($this->heures_tunnel ?? 24) - 24;
        $poids = $this->poids_sortie ?? $this->poids_entree;
        $jours = $heuresExcedentaires / 24;

        return round($poids * $jours * $tarif * 1.5);
    }

    /**
     * Marquer comme payé selon le type d'infrastructure
     */
    public function marquerPaye(): bool
    {
        if ($this->type_infrastructure === 'tunnel') {
            $this->paye_tunnel = true;
        } else {
            $this->paye_chambre = true;
        }
        return $this->save();
    }

    /**
     * Vérifier si le lot est complètement payé
     */
    public function estCompletementPaye(): bool
    {
        if ($this->type_infrastructure === 'tunnel') {
            return $this->paye_tunnel;
        }
        return $this->paye_chambre;
    }

    /**
     * Marquer comme sorti
     */
    public function marquerSorti(): bool
    {
        $this->statut = 'sorti';
        $this->date_sortie = now();
        return $this->save();
    }

    /**
     * Calculer le nombre de jours en stock
     */
    public function getJoursEnStockAttribute(): int
    {
        $date = $this->date_entree_chambre ?? $this->created_at;
        return (int) $date->diffInDays(now());
    }

    /**
     * Vérifier si le lot est urgent (>5 jours en chambre)
     */
    public function getEstUrgentAttribute(): bool
    {
        if ($this->type_infrastructure !== 'chambre') {
            return false;
        }
        return $this->jours_en_stock > 5;
    }

    /**
     * Obtenir le statut du paiement en texte
     */
    public function getStatutPaiementAttribute(): string
    {
        if ($this->type_infrastructure === 'tunnel') {
            return $this->paye_tunnel ? 'Payé' : 'En attente';
        }
        return $this->paye_chambre ? 'Payé' : 'En attente';
    }
    /**
     * Il n'existe pas de colonne type_infrastructure : on le déduit de l'emplacement du lot.
     */
    public function getTypeInfrastructureAttribute(): ?string
    {
        if ($this->chambre_id) {
            return 'chambre';
        }

        return $this->tunnel_id ? 'tunnel' : null;
    }

    public function getNumeroAttribute()
    {
        return 'LOT-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
    public function sortiesChambre()
    {
        return $this->hasMany(SortieChambre::class);
    }
       public function sortiesTunnel()
    {
        return $this->hasMany(SortieTunnel::class);
    }


}
