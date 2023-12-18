<?php

namespace Pteranodon\Transformers\Api\Client;

use Pteranodon\Models\Egg;

class EggTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Egg::RESOURCE_NAME;
    }

    public function transform(Egg $egg): array
    {
        return [
            'name' => $egg->name,
        ];
    }
}
