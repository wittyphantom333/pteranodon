<?php

namespace Pteranodon\Http\Requests\Api\Client\Servers\Subusers;

use Pteranodon\Models\Permission;

class DeleteSubuserRequest extends SubuserRequest
{
    public function permission(): string
    {
        return Permission::ACTION_USER_DELETE;
    }
}
