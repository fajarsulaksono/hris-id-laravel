<?php

namespace App\Support\Concerns;

use Illuminate\Support\Facades\Auth;

trait Blameable
{
    protected static function bootBlameable(): void
    {
        static::creating(function ($model) {
            $user = Auth::user();
            if ($user && empty($model->created_by)) {
                $model->created_by = $user->getAuthIdentifier();
            }
            if ($user && empty($model->updated_by)) {
                $model->updated_by = $user->getAuthIdentifier();
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->getAuthIdentifier();
            }
        });

        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                return;
            }

            $user = Auth::user();
            if ($user && empty($model->deleted_by)) {
                $model->deleted_by = $user->getAuthIdentifier();
            }
        });

        static::restoring(function ($model) {
            $model->deleted_by = null;
        });
    }

    public function initializeBlameable(): void
    {
        $this->fillable[] = 'created_by';
        $this->fillable[] = 'updated_by';
        $this->fillable[] = 'deleted_by';
    }
}