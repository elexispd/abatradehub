<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class ModelAuditObserver
{
    protected array $ignored = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
        'last_login_at',
    ];

    public function created(Model $model): void
    {
        $modelName = class_basename($model);
        $title = $model->name ?? ($model->reference ?? "#{$model->getKey()}");

        AuditLog::record(
            action: 'created',
            description: "Created {$modelName}: {$title}",
            auditable: $model,
            newValues: $this->cleanAttributes($model->getAttributes()),
        );
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $dirtyKeys = array_diff(array_keys($changes), $this->ignored);

        if (empty($dirtyKeys)) {
            return;
        }

        $old = [];
        $new = [];

        foreach ($dirtyKeys as $key) {
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $model->getAttribute($key);
        }

        $modelName = class_basename($model);
        $title = $model->name ?? ($model->reference ?? "#{$model->getKey()}");

        $action = 'updated';
        $desc = "Updated {$modelName}: {$title}";

        if (array_key_exists('status', $new)) {
            $action = 'status_changed';
            $statusVal = is_object($new['status']) && enum_exists(get_class($new['status'])) ? $new['status']->value : (string) $new['status'];
            $desc = "Changed {$modelName} ({$title}) status to [{$statusVal}]";
        } elseif (array_key_exists('is_active', $new)) {
            $action = $new['is_active'] ? 'enabled' : 'disabled';
            $desc = ($new['is_active'] ? 'Activated' : 'Deactivated')." {$modelName}: {$title}";
        }

        AuditLog::record(
            action: $action,
            description: $desc,
            auditable: $model,
            oldValues: $old,
            newValues: $new,
        );
    }

    public function deleted(Model $model): void
    {
        $modelName = class_basename($model);
        $title = $model->name ?? ($model->reference ?? "#{$model->getKey()}");

        AuditLog::record(
            action: 'deleted',
            description: "Deleted {$modelName}: {$title}",
            auditable: $model,
            oldValues: $this->cleanAttributes($model->getAttributes()),
        );
    }

    public function restored(Model $model): void
    {
        $modelName = class_basename($model);
        $title = $model->name ?? ($model->reference ?? "#{$model->getKey()}");

        AuditLog::record(
            action: 'restored',
            description: "Restored {$modelName}: {$title}",
            auditable: $model,
        );
    }

    protected function cleanAttributes(array $attributes): array
    {
        foreach ($this->ignored as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}

