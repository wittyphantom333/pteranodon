<?php

namespace Pteranodon\Transformers\Api\Application;

use Pteranodon\Models\Database;
use Pteranodon\Models\DatabaseHost;
use League\Fractal\Resource\Item;
use Pteranodon\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\NullResource;
use Illuminate\Contracts\Encryption\Encrypter;

class ServerDatabaseTransformer extends BaseTransformer
{
    protected array $availableIncludes = ['password', 'host'];

    private Encrypter $encrypter;

    /**
     * Perform dependency injection.
     */
    public function handle(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Database::RESOURCE_NAME;
    }

    /**
     * Transform a database model in a representation for the application API.
     */
    public function transform(Database $model): array
    {
        return [
            'id' => $model->id,
            'server' => $model->server_id,
            'host' => $model->database_host_id,
            'database' => $model->database,
            'username' => $model->username,
            'remote' => $model->remote,
            'max_connections' => $model->max_connections,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }

    /**
     * Include the database password in the request.
     */
    public function includePassword(Database $model): Item
    {
        return $this->item($model, function (Database $model) {
            return [
                'password' => $this->encrypter->decrypt($model->password),
            ];
        }, 'database_password');
    }

    /**
     * Return the database host relationship for this server database.
     *
     * @throws \Pteranodon\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeHost(Database $model): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_DATABASE_HOSTS)) {
            return $this->null();
        }

        $model->loadMissing('host');

        return $this->item(
            $model->getRelation('host'),
            $this->makeTransformer(DatabaseHostTransformer::class),
            DatabaseHost::RESOURCE_NAME
        );
    }
}
