<?php

namespace Pteranodon\Http\Requests\Api\Application\Users;

use Pteranodon\Services\Acl\Api\AdminAcl as Acl;
use Pteranodon\Http\Requests\Api\Application\ApplicationApiRequest;

class GetUsersRequest extends ApplicationApiRequest
{
    protected ?string $resource = Acl::RESOURCE_USERS;

    protected int $permission = Acl::READ;
}
