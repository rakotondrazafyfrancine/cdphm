<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Copie fidèle d'un bon ou d'une facture émis : sert de preuve en cas de problème. */
class Archive extends Model
{
    protected $table = 'archives_documents';

    protected $guarded = [];

    protected $casts = [
        'contenu' => 'array',
        'poids_total' => 'decimal:2',
        'montant_total' => 'decimal:2',
    ];

    public const TYPES = [
        'bon_entree' => ["Bon d'entrée", 'BE'],
        'bon_sortie' => ['Bon de sortie', 'BS'],
        'facture' => ['Facture', 'FA'],
    ];

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type][0] ?? $this->type;
    }
}
