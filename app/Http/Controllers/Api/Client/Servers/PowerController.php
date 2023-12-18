<?php

namespace Pteranodon\Http\Controllers\Api\Client\Servers;

use Pteranodon\Models\Server;
use Illuminate\Http\Response;
use Pteranodon\Facades\Activity;
use Pteranodon\Repositories\Wings\DaemonPowerRepository;
use Pteranodon\Http\Controllers\Api\Client\ClientApiController;
use Pteranodon\Http\Requests\Api\Client\Servers\SendPowerRequest;

class PowerController extends ClientApiController
{
    /**
     * PowerController constructor.
     */
    public function __construct(private DaemonPowerRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Send a power action to a server.
     */
    public function index(SendPowerRequest $request, Server $server): Response
    {
        $this->repository->setServer($server)->send(
            $request->input('signal')
        );

        Activity::event(strtolower("server:power.{$request->input('signal')}"))->log();

        return $this->returnNoContent();
    }
}
