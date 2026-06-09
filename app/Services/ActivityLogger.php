<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(?User $user, string $action, ?Model $subject = null, array $properties = []): void
    {
        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()?->ip(),
        ]);
    }
}
