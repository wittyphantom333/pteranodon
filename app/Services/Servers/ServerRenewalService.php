<?php

namespace Pteranodon\Services\Servers;

use Pteranodon\Models\Server;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Http\Requests\Api\Client\ClientApiRequest;
use Pteranodon\Contracts\Repository\SettingsRepositoryInterface;

class ServerRenewalService
{
    private SuspensionService $suspensionService;
    private SettingsRepositoryInterface $settings;

    /**
     * ServerRenewalService constructor.
     */
    public function __construct(
        SuspensionService $suspensionService,
        SettingsRepositoryInterface $settings
    ) {
        $this->settings = $settings;
        $this->suspensionService = $suspensionService;
    }

    /**
     * Renews a server.
     *
     * @throws DisplayException
     */
    public function handle(ClientApiRequest $request, Server $server): Server
    {
        $user = $request->user();
        $cost = $this->settings->get('pteranodon::renewal:cost', 200);

        if ($user->store_balance < $cost) {
            throw new DisplayException('You do not have enough credits to renew your server.');
        }

        try {
            $user->update(['store_balance' => $user->store_balance - $cost]);
            $server->update(['renewal' => $server->renewal + $this->settings->get('pteranodon::renewal:default', 7)]);
        } catch (DisplayException $ex) {
            throw new DisplayException('An unexpected error occured while trying to renew your server.');
        }

        if ($server->status == 'suspended' && $server->renewal >= 0) {
            $this->suspensionService->toggle($server, 'unsuspend');
        }

        return $server->refresh();
    }
}
