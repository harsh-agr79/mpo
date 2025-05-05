<?php

namespace App\Models\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasActivityLogs
{
   public function activityLogs(): HasMany
   {
      return $this->hasMany(ActivityLog::class, 'primary_key_value')
         ->where('table_name', $this->getTable());
   }
}
