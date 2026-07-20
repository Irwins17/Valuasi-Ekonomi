<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::log(
                'created',
                get_class($model),
                $model->id,
                $model->getTable(),
                null,
                $model->toArray()
            );
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();

            if (!empty($newValues)) {
                AuditLog::log(
                    'updated',
                    get_class($model),
                    $model->id,
                    $model->getTable(),
                    array_intersect_key($oldValues, $newValues),
                    $newValues
                );
            }
        });

        static::deleted(function ($model) {
            AuditLog::log(
                'deleted',
                get_class($model),
                $model->id,
                $model->getTable(),
                $model->toArray(),
                null
            );
        });
    }
}
