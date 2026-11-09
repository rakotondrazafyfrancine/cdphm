<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'journaux';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array',
        'created_at' => 'datetime',
    ];

    public static function enregistrer(string $action, string $description, array $extra = []): void
    {
        try {
            static::create(array_merge([
                'user_id' => auth()->id(),
                'user_pseudo' => auth()->user()?->pseudo,
                'action' => $action,
                'description' => $description,
                'ip' => request()?->ip(),
            ], $extra));
        } catch (\Throwable) {
            // Le journal ne doit jamais bloquer l'application.
        }
    }
}
