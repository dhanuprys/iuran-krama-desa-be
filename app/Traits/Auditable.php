<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::logAudit('CREATE', $model);
        });

        static::updated(function (Model $model) {
            self::logAudit('UPDATE', $model);
        });

        static::deleted(function (Model $model) {
            self::logAudit('DELETE', $model);
        });
    }

    protected static function logAudit(string $action, Model $model)
    {
        // Skip logging if no user is authenticated (e.g. seeder or console)
        // Unless we want to log system actions too, but usually audit logs track user actions.
        if (!auth()->check()) {
            return;
        }

        $oldValues = null;
        $newValues = null;

        if ($action === 'UPDATE') {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
        } elseif ($action === 'CREATE') {
            $newValues = $model->getAttributes();
        } elseif ($action === 'DELETE') {
            $oldValues = $model->getAttributes();
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
