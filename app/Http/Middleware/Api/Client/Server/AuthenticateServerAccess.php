<?php

namespace Pteranodon\Http\Middleware\Api\Client\Server;

use Pteranodon\Models\Server;
use Illuminate\Http\Request;
use Pteranodon\Exceptions\Http\Server\ServerStateConflictException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticateServerAccess
{
    /**
     * Routes that this middleware should not apply to if the user is an admin.
     */
    protected array $except = [
        'api:client:server.ws',
    ];

    /**
     * AuthenticateServerAccess constructor.
     */
    public function __construct()
    {
    }

    /**
     * Authenticate that this server exists and is not suspended or marked as installing.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var \Pteranodon\Models\User $user */
        $user = $request->user();
        $server = $request->route()->parameter('server');

        if (!$server instanceof Server) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }

        // At the very least, ensure that the user trying to make this request is the
        // server owner, a subuser, or a root admin. We'll leave it up to the controllers
        // to authenticate more detailed permissions if needed.
        if ($user->id !== $server->owner_id && !$user->root_admin) {
            // Check for subuser status.
            if (!$server->subusers->contains('user_id', $user->id)) {
                throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
            }
        }

        if (!$request->routeIs(['api:client:server.delete', 'api:client:server.renew'])) {
            try {
                $server->validateCurrentState();
            } catch (ServerStateConflictException $exception) {
                // Still allow users to get information about their server if it is installing or
                // being transferred.
                if (!$request->routeIs('api:client:server.view')) {
                    if (($server->isSuspended() || $server->node->isUnderMaintenance()) && !$request->routeIs('api:client:server.resources')) {
                        throw $exception;
                    }
                    if (!$user->root_admin || !$request->routeIs($this->except)) {
                        throw $exception;
                    }
                }
            }
        }

        $request->attributes->set('server', $server);

        return $next($request);
    }
}
