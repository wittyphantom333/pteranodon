<?php

namespace Pteranodon\Facades;

use Illuminate\Support\Facades\Facade;
use Pteranodon\Services\Activity\ActivityLogService;

class Activity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogService::class;
    }
}
