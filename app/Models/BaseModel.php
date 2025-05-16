<?php

namespace App\Models;

use App\Models\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class BaseModel extends Model
{
   use HasActivityLogs;
   public static function booted()
   {
      static::created(function ($model) {
         self::logActivity('created', $model, null, $model->getAttributes());
      });

      static::updated(function ($model) {
         self::logActivity('updated', $model, $model->getOriginal(), $model->getChanges());
      });

      static::deleted(function ($model) {
         self::logActivity('deleted', $model, $model->getOriginal(), null);
      });

      if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
         static::restored(function ($model) {
            self::logActivity('restored', $model, null, $model->getAttributes());
         });

         static::forceDeleted(function ($model) {
            self::logActivity('forceDeleted', $model, $model->getOriginal(), null);
         });
      }
   }

   protected static function logActivity(string $operation, Model $model, ?array $oldData, ?array $newData): void
   {
      // Avoid logging the activity log itself to prevent infinite loops
      if ($model instanceof ActivityLog) {
         return;
      }

      $admin = Auth::user();
      if (!$admin instanceof \App\Models\Admin) {
         return;
      }

      ActivityLog::create([
         'table_name' => $model->getTable(),
         'primary_key_value' => $model->getKey(),
         'operation' => $operation,
         'old_data' => $oldData ? json_encode($oldData) : null,
         'new_data' => $newData ? json_encode($newData) : null,
         'admin_id' => Auth::id(),
      ]);
   }
}
