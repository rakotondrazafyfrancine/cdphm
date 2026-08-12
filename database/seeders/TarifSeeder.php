<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;

class TarifSeeder extends Seeder
{
    public function run(): void
    {
        $tarifs = [
            // ==========================================
            // 1. CONGÉLATION (Ar/Kg)
            // ==========================================
            ['categorie' => 'congelation', 'designation' => 'Rapide', 'montant' => 450],
            ['categorie' => 'congelation', 'designation' => 'Société', 'montant' => 15],
            ['categorie' => 'congelation', 'designation' => 'Collecteur/Maréyeur/Pêcheurs (Poisson et Crabe)', 'montant' => 25],
            ['categorie' => 'congelation', 'designation' => 'Collecteur/Maréyeur/Pêcheurs (Crevettes et Langoustes)', 'montant' => 50],

            // EAU (Ar/Kg)
            ['categorie' => 'congelation', 'designation' => 'Participation eau (poissons, crevettes, ...)', 'montant' => 50],
            ['categorie' => 'congelation', 'designation' => 'Participation eau (crabe avec traitement)', 'montant' => 100],
            ['categorie' => 'congelation', 'designation' => 'Participation eau (crabe sans traitement)', 'montant' => 200],
        ];
    }
}
