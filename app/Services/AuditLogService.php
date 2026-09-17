<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public static function log(string $action, string $module, ?string $description = null, $oldData = null, $newData = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->user()?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }

    public static function created(string $module, $model, ?string $description = null): AuditLog
    {
        return self::log('create', $module, $description ?? "Membuat {$module} baru", null, $model->toArray());
    }

    public static function updated(string $module, $model, $oldData, $newData, ?string $description = null): AuditLog
    {
        return self::log('update', $module, $description ?? "Memperbarui {$module}", $oldData, $newData);
    }

    public static function deleted(string $module, $model, ?string $description = null): AuditLog
    {
        return self::log('delete', $module, $description ?? "Menghapus {$module}", $model->toArray(), null);
    }
}
