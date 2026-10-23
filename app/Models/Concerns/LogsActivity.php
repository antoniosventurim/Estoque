<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogMap;
use Spatie\Activitylog\Models\Concerns\LogsActivity as SpatieLogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Registra criações, alterações, exclusões e restaurações do model
 * no log de atividades ("estoque") com atributos alterados (dirty).
 */
trait LogsActivity
{
    use SpatieLogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('estoque')
            ->setDescriptionForEvent(
                fn (string $eventName) => ActivityLogMap::descriptionFor($eventName, $this)
            );
    }
}
