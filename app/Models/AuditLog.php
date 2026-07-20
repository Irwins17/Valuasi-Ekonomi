<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'event', 'model_type', 'model_id', 'table_name',
        'changes', 'old_values', 'new_values', 'ip_address', 'user_agent',
        'description'
    ];

    protected $casts = [
        'changes' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log($event, $modelType, $modelId, $tableName, $oldValues = null, $newValues = null)
    {
        $user = auth()->user();
        
        return self::create([
            'user_id' => $user?->id,
            'event' => $event,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'table_name' => $tableName,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
