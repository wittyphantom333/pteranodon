<?php

namespace Pteranodon\Services\Servers;

use Pteranodon\Models\User;
use Pteranodon\Models\Server;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\ConnectionInterface;
use Pteranodon\Repositories\Wings\DaemonServerRepository;
use Pteranodon\Services\Databases\DatabaseManagementService;
use Pteranodon\Exceptions\Http\Connection\DaemonConnectionException;

class ServerDeletionService
{
    protected bool $force = false;
    protected bool $return_resources = false;

    /**
     * ServerDeletionService constructor.
     */
    public function __construct(
        private ConnectionInterface $connection,
        private DaemonServerRepository $daemonServerRepository,
        private DatabaseManagementService $databaseManagementService
    ) {
    }

    /**
     * Set if the server should be forcibly deleted from the panel (ignoring daemon errors) or not.
     */
    public function withForce(bool $bool = true): self
    {
        $this->force = $bool;

        return $this;
    }

    /**
     * Set if the server's owner should recieve the resources upon server deletion.
     *
     * @return $this
     */
    public function returnResources(bool $bool = true): self
    {
        $this->return_resources = $bool;

        return $this;
    }

    /**
     * Delete a server from the panel and remove any associated databases from hosts.
     *
     * @throws \Throwable
     * @throws \Pteranodon\Exceptions\DisplayException
     */
    public function handle(Server $server): void
    {
        try {
            $this->daemonServerRepository->setServer($server)->delete();
        } catch (DaemonConnectionException $exception) {
            // If there is an error not caused a 404 error and this isn't a forced delete,
            // go ahead and bail out. We specifically ignore a 404 since that can be assumed
            // to be a safe error, meaning the server doesn't exist at all on Wings so there
            // is no reason we need to bail out from that.
            if (!$this->force && $exception->getStatusCode() !== Response::HTTP_NOT_FOUND) {
                throw $exception;
            }

            Log::warning($exception);
        }

        $this->connection->transaction(function () use ($server) {
            foreach ($server->databases as $database) {
                try {
                    $this->databaseManagementService->delete($database);
                } catch (\Exception $exception) {
                    if (!$this->force) {
                        throw $exception;
                    }

                    // Oh well, just try to delete the database entry we have from the database
                    // so that the server itself can be deleted. This will leave it dangling on
                    // the host instance, but we couldn't delete it anyways so not sure how we would
                    // handle this better anyways.
                    //
                    // @see https://github.com/pterodactyl/panel/issues/2085
                    $database->delete();

                    Log::warning($exception);
                }
            }

            $server->delete();
        });

        if (!$this->return_resources) {
            return;
        }

        try {
            $user = User::findOrFail($server->owner_id);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $user->update([
            'store_cpu' => $user->store_cpu + $server->cpu,
            'store_memory' => $user->store_memory + $server->memory,
            'store_disk' => $user->store_disk + $server->disk,
            'store_slots' => $user->store_slots + 1, // Always one slot.
            'store_ports' => $user->store_ports + $server->allocation_limit,
            'store_backups' => $user->store_backups + $server->backup_limit,
            'store_databases' => $user->store_databases + $server->database_limit,
        ]);
    }
}
