<?php

namespace Pteranodon\Transformers\Api\Application;

use Pteranodon\Models\Database;
use Pteranodon\Models\DatabaseHost;
use Pteranodon\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class DatabaseHostTransformer extends BaseTransformer
{
    protected array $availableIncludes = [
        'databases',
    ];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return DatabaseHost::RESOURCE_NAME;
    }

    /**
     * Transform database host into a representation for the application API.
     */
    public function transform(DatabaseHost $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'host' => $model->host,
            'port' => $model->port,
            'username' => $model->username,
            'node' => $model->node_id,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }

    /**
     * Include the databases associated with this host.
     *
     * @throws \Pteranodon\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeDatabases(DatabaseHost $model): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVER_DATABASES)) {
            return $this->null();
        }

        $model->loadMissing('databases');

        return $this->collection($model->getRelation('databases'), $this->makeTransformer(ServerDatabaseTransformer::class), Database::RESOURCE_NAME);
    }
}
