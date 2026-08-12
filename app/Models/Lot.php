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
        'espece',
        'categorie',
        'contact',
        'dockers',
        'date_entree',

        // Poids
        'poids_entree',
        'poids_sortie',

        // Infrastructure
        'tunnel_id',
        'chambre_id',
        'type_infrastructure',

        // Gestion du temps
        'heures_tunnel',
        'duree_stockage',
        'duree_indeterminee',


        // Frais
        'frais_transport',
        'frais_congelation',
        'frais_total',

        // Paiements
        'paye_tunnel',
        'paye_chambre',
        'montant_tunnel',
        'penalite_tunnel',

        // Dates
        'date_entree_chambre',
        'date_sortie',

        // Autres
        'avec_traitement',
        'responsabilite_dep',
        'statut',
    ];

    protected $casts = [
        'poids_entree' => 'decimal:2',
        'poids_sortie' => 'decimal:2',
        'frais_transport' => 'decimal:2',
        'frais_congelation' => 'decimal:2',
        'frais_total' => 'decimal:2',
        'montant_tunnel' => 'decimal:2',
        'penalite_tunnel' => 'decimal:2',
        'tarif_transport_libre' => 'decimal:2',
        'heures_tunnel' => 'float',
        'duree_stockage' => 'float',
        'paye_tunnel' => 'boolean',
        'paye_chambre' => 'boolean',
        'duree_indeterminee' => 'boolean',
        'avec_traitement' => 'boolean',
        'transport_avec_go' => 'boolean',
        'date_entree_chambre' => 'datetime',
        'date_sortie' => 'datetime',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /**
     * Relation avec le client
     */
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
            return now()->diffInDays($this->date_entree_chambre) + 1;
        }
        return $this->duree_stockage ?? 1;
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

        $jours = $this->duree_stockage ?? 1;
        return $jours . ' jour(s)';
    }

    /**
     * Obtenir le poids final (sortie)
     */
    public function getPoidsFinalAttribute(): float
    {
        return $this->poids_sortie ?? $this->poids_entree;
    }

    /**
     * Obtenir le montant total des frais formaté
     */
    public function getMontantTotalFormateAttribute(): string
    {
        return number_format($this->frais_total ?? 0, 2) . ' Ar';
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
        return $query->where('type_infrastructure', 'tunnel')
            ->where('statut', 'en_stock');
    }

    /**
     * Scope pour les lots en chambre
     */
    public function scopeEnChambre($query)
    {
        return $query->where('type_infrastructure', 'chambre')
            ->where('statut', 'en_stock');
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
        return $query->where('type_infrastructure', 'tunnel')
            ->where('heures_tunnel', '>', 24)
            ->where('statut', 'en_stock');
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
     * Scope pour les lots avec une destination de transport
     */
    public function scopeAvecDestination($query)
    {
        return $query->whereNotNull('destination');
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
        return now()->diffInDays($date);
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
     * Vérifier si le lot a un transport associé
     */
    public function getATransportAttribute(): bool
    {
        return $this->missions()->exists();
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
    public function getNumeroAttribute()
    {
        return 'LOT-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
