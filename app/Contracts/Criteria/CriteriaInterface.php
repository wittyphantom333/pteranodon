<?php

namespace Pteranodon\Contracts\Criteria;

use Pteranodon\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

interface CriteriaInterface
{
    /**
     * Apply selected criteria to a repository call.
     */
    public function apply(Model $model, Repository $repository): mixed;
}
