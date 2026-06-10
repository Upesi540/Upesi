<?php
namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasOwner
{
    /**
     * Définit le nom de la colonne de propriété.
     * Peut être écrasé dans le Modèle.
     */
    public function getOwnerColumn(): string
    {
        return 'user_id'; // Nom par défaut
    }

    protected static function bootHasOwner(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                $column = $model->getOwnerColumn();

                if ($user instanceof User) {
                    if (!$user->hasAnyRole(['admin', 'super_admin'])) {
                        $model->{$column} = $user->id;
                    }
                    elseif (empty($model->{$column})) {
                        $model->{$column} = $user->id;
                    }
                }
            }
        });
    }

    public function scopeMine(Builder $query): Builder
    {
        if (!Auth::check()) return $query;

        $user = Auth::user();
        if ($user instanceof User && $user->hasAnyRole(['admin', 'super_admin'])) {
            return $query;
        }

        return $query->where($this->getOwnerColumn(), $user->id);
    }

    public function isOwnedBy($user): bool
    {
        if (!$user) return false;
        if ($user->hasAnyRole(['admin', 'super_admin'])) return true;

        return $this->{$this->getOwnerColumn()} === $user->id;
    }
}
