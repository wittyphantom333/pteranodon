<?php

namespace Pteranodon\Http\Requests\Api\Client;

use Pteranodon\Models\Server;
use Pteranodon\Contracts\Http\ClientPermissionsRequest;
use Pteranodon\Http\Requests\Api\Application\ApplicationApiRequest;

/**
 * @method \Pteranodon\Models\User user($guard = null)
 */
class ClientApiRequest extends ApplicationApiRequest
{
    /**
     * Determine if the current user is authorized to perform the requested action against the API.
     */
    public function authorize(): bool
    {
        if ($this instanceof ClientPermissionsRequest || method_exists($this, 'permission')) {
            $server = $this->route()->parameter('server');

            if ($server instanceof Server) {
                return $this->user()->can($this->permission(), $server);
            }

            // If there is no server available on the reqest, trigger a failure since
            // we expect there to be one at this point.
            return false;
        }

        return true;
    }
}
