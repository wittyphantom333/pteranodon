<?php

namespace Pteranodon\Listeners\Auth;

use Illuminate\Http\Request;
use Pteranodon\Facades\Activity;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetListener
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(PasswordReset $event): void
    {
        Activity::event('event:password-reset')
            ->withRequestMetadata()
            ->subject($event->user)
            ->log();
    }
}
