<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $primaryKey = 'cle';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['cle', 'valeur'];

    /** @var array<string, ?string> valeurs déjà lues pendant la requête en cours */
    private static array $memo = [];

    public static function lire(string $cle, ?string $defaut = null): ?string
    {
        if (! array_key_exists($cle, self::$memo)) {
            try {
                self::$memo[$cle] = static::find($cle)?->valeur;
            } catch (\Throwable) {
                return $defaut;
            }
        }

        return self::$memo[$cle] ?? $defaut;
    }

    public static function ecrire(string $cle, ?string $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        unset(self::$memo[$cle]);
    }
}
