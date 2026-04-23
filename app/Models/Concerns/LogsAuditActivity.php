<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAuditActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('audit_trail')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $ipAddress = request()?->ip();
        $userAgent = request()?->userAgent();

        $activity->properties = $activity->properties->merge([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'event_name' => $eventName,
        ]);
    }
}
