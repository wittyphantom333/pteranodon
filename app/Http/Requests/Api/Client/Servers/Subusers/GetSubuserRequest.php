<?php

namespace Pteranodon\Http\Requests\Api\Client\Servers\Subusers;

use Pteranodon\Models\Permission;

class GetSubuserRequest extends SubuserRequest
{
    /**
     * Confirm that a user is able to view subusers for the specified server.
     */
    public function permission(): string
    {
        return Permission::ACTION_USER_READ;
    }
}
