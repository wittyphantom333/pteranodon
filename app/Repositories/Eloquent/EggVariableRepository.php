<?php

namespace Pteranodon\Repositories\Eloquent;

use Pteranodon\Models\EggVariable;
use Illuminate\Support\Collection;
use Pteranodon\Contracts\Repository\EggVariableRepositoryInterface;

class EggVariableRepository extends EloquentRepository implements EggVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return EggVariable::class;
    }

    /**
     * Return editable variables for a given egg. Editable variables must be set to
     * user viewable in order to be picked up by this function.
     */
    public function getEditableVariables(int $egg): Collection
    {
        return $this->getBuilder()->where([
            ['egg_id', '=', $egg],
            ['user_viewable', '=', 1],
            ['user_editable', '=', 1],
        ])->get($this->getColumns());
    }
}
