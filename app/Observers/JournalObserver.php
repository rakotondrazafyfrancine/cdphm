<?php

namespace App\Observers;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Model;

class JournalObserver
{
    private const IGNORES = ['created_at', 'updated_at', 'password', 'remember_token'];

    public function created(Model $model): void
    {
        $this->log('creation', $model, null, $this->nettoyer($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->nettoyer($model->getChanges());
        if (! $changes) {
            return;
        }
        $anciennes = array_intersect_key($this->nettoyer($model->getRawOriginal()), $changes);
        $this->log('modification', $model, $anciennes, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('suppression', $model, $this->nettoyer($model->getAttributes()), null);
    }

    private function log(string $action, Model $model, ?array $anciennes, ?array $nouvelles): void
    {
        $nom = class_basename($model);
        $verbe = ['creation' => 'créé', 'modification' => 'modifié', 'suppression' => 'supprimé'][$action];

        Journal::enregistrer($action, "{$nom} #{$model->getKey()} {$verbe}", [
            'modele' => $nom,
            'modele_id' => is_numeric($model->getKey()) ? $model->getKey() : null,
            'anciennes_valeurs' => $anciennes,
            'nouvelles_valeurs' => $nouvelles,
        ]);
    }

    private function nettoyer(array $valeurs): array
    {
        return array_diff_key($valeurs, array_flip(self::IGNORES));
    }
}
