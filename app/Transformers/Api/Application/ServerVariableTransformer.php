<?php

namespace Pteranodon\Transformers\Api\Application;

use Pteranodon\Models\EggVariable;
use League\Fractal\Resource\Item;
use Pteranodon\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\NullResource;

class ServerVariableTransformer extends BaseTransformer
{
    /**
     * List of resources that can be included.
     */
    protected array $availableIncludes = ['parent'];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return ServerVariable::RESOURCE_NAME;
    }

    /**
     * Return a generic transformed server variable array.
     */
    public function transform(EggVariable $variable): array
    {
        return $variable->toArray();
    }

    /**
     * Return the parent service variable data.
     *
     * @throws \Pteranodon\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeParent(EggVariable $variable): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_EGGS)) {
            return $this->null();
        }

        $variable->loadMissing('variable');

        return $this->item($variable->getRelation('variable'), $this->makeTransformer(EggVariableTransformer::class), 'variable');
    }
}
