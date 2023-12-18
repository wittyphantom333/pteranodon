<?php

namespace Pteranodon\Http\Controllers\Api\Application\Users;

use Pteranodon\Models\User;
use Illuminate\Http\Response;
use Pteranodon\Services\Users\UserUpdateService;
use Pteranodon\Contracts\Repository\UserRepositoryInterface;
use Pteranodon\Http\Requests\Api\Application\Users\GetUsersRequest;
use Pteranodon\Http\Requests\Api\Application\Users\UpdateUserRequest;
use Pteranodon\Transformers\Api\Application\UserResourcesTransformer;
use Pteranodon\Http\Controllers\Api\Application\ApplicationApiController;

class UserResourcesController extends ApplicationApiController
{
    /**
     * @var \Pteranodon\Contracts\Repository\UserRepositoryInterface
     */
    private $repository;

    /**
     * @var \Pteranodon\Services\Users\UserUpdateService
     */
    private $updateService;

    /**
     * UserController constructor.
     */
    public function __construct(
        UserUpdateService $updateService,
        UserRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->updateService = $updateService;
    }

    /**
     * View a user's resources.
     */
    public function view(GetUsersRequest $request, User $user): array
    {
        return $this->fractal->item($user)
            ->transformWith($this->getTransformer(UserResourcesTransformer::class))
            ->toArray()
            ->respond(201);
    }

    /**
     * Update an existing user on the system and return the response. Returns the
     * updated user model response on success. Supports handling of token revocation
     * errors when switching a user from an admin to a normal user.
     *
     * Revocation errors are returned under the 'revocation_errors' key in the response
     * meta. If there are no errors this is an empty array.
     *
     * @throws \Pteranodon\Exceptions\Model\DataValidationException
     * @throws \Pteranodon\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateUserRequest $request, User $user): array
    {
        // TODO: use userUpdateService in future.
        $user->update([
            'store_balance' => $request->input('store_balance'),
            'store_cpu' => $request->input('store_cpu'),
            'store_memory' => $request->input('store_memory'),
            'store_disk' => $request->input('store_disk'),
            'store_slots' => $request->input('store_slots'),
            'store_ports' => $request->input('store_ports'),
            'store_backups' => $request->input('store_backups'),
            'store_databases' => $request->input('store_databases'),
        ]);

        return $this->fractal->item($user)
            > transformWith($this->getTransformer(UserResourcesTransformer::class))
            ->toArray()
            ->respond(201);
    }
}
