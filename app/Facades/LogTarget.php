<?php

namespace Pteranodon\Facades;

use Illuminate\Support\Facades\Facade;
use Pteranodon\Services\Activity\ActivityLogTargetableService;

class LogTarget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogTargetableService::class;
    }
}
