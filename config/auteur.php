<?php

// Informations de l'auteur, affichées en pied de la page de connexion.
// Valeurs modifiables dans .env : AUTEUR_NOM, AUTEUR_EMAIL, AUTEUR_TELEPHONE, AUTEUR_ADRESSE.
return [
    'nom' => env('AUTEUR_NOM', 'RAKOTONDRAZAFY Ramanganirina Francine'),
    'email' => env('AUTEUR_EMAIL', 'ramanganirinafrancine@gmail.com'),
    'telephone' => env('AUTEUR_TELEPHONE', '032 58 065 42'),
    'adresse' => env('AUTEUR_ADRESSE', 'Ambondrona, Mahajanga'),
];
