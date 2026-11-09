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
        $title = $model->name ?? ($model->reference ?? ($model->invoice_number ?? "#{$model->getKey()}"));

        if ($model instanceof \App\Models\Sale) {
            if (empty($model->created_by)) {
                $cName = trim($model->customer_name ?? '') ?: 'Customer';
                $desc = "Online wholesale order {$model->invoice_number} placed by customer {$cName}";
            } else {
                $staffName = auth()->user()?->name ?? 'Staff';
                $desc = "Staff {$staffName} created Sale: {$model->invoice_number}";
            }
        } else {
            $desc = "Created {$modelName}: {$title}";
        }

        AuditLog::record(
            action: 'created',
            description: $desc,
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
        $title = $model->name ?? ($model->reference ?? ($model->invoice_number ?? "#{$model->getKey()}"));

        $action = 'updated';
        $desc = "Updated {$modelName}: {$title}";

        if ($model instanceof \App\Models\Sale && array_key_exists('status', $new)) {
            $action = 'status_changed';
            $statusVal = is_object($new['status']) && enum_exists(get_class($new['status'])) ? $new['status']->value : (string) $new['status'];
            $statusLabel = ucfirst(str_replace('_', ' ', $statusVal));
            $desc = "Changed order {$title} status to [{$statusLabel}]";
        } elseif ($model instanceof \App\Models\Sale && array_key_exists('payment_status', $new)) {
            $action = 'payment_status_changed';
            $payVal = is_object($new['payment_status']) && enum_exists(get_class($new['payment_status'])) ? $new['payment_status']->value : (string) $new['payment_status'];
            $payLabel = ucfirst(str_replace('_', ' ', $payVal));
            $desc = "Changed order {$title} payment status to [{$payLabel}]";
        } elseif (array_key_exists('status', $new)) {
            $action = 'status_changed';
            $statusVal = is_object($new['status']) && enum_exists(get_class($new['status'])) ? $new['status']->value : (string) $new['status'];
            $desc = "Changed {$modelName} ({$title}) status to [{$statusVal}]";
        } elseif (array_key_exists('payment_status', $new)) {
            $action = 'payment_status_changed';
            $payVal = is_object($new['payment_status']) && enum_exists(get_class($new['payment_status'])) ? $new['payment_status']->value : (string) $new['payment_status'];
            $desc = "Changed {$modelName} ({$title}) payment status to [{$payVal}]";
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
        $title = $model->name ?? ($model->reference ?? ($model->invoice_number ?? "#{$model->getKey()}"));

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
        $title = $model->name ?? ($model->reference ?? ($model->invoice_number ?? "#{$model->getKey()}"));

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

