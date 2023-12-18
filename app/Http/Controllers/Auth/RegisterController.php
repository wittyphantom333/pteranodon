<?php

namespace Pteranodon\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Http\Requests\Auth\RegisterRequest;
use Pteranodon\Services\Users\UserCreationService;
use Pteranodon\Exceptions\Model\DataValidationException;
use Pteranodon\Contracts\Repository\SettingsRepositoryInterface;

class RegisterController extends AbstractLoginController
{
    /**
     * RegisterController constructor.
     */
    public function __construct(private UserCreationService $creationService, private SettingsRepositoryInterface $settings)
    {
        parent::__construct();
    }

    /**
     * Handle a register request to the application.
     *
     * @throws DataValidationException|DisplayException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $approved = false;
        $verified = false;
        $prefix = 'pteranodon::registration:';

        if ($this->settings->get($prefix . 'enabled') != 'true') {
            throw new DisplayException('Unable to register user.');
        }

        if (!$this->settings->get($prefix . 'verification')) {
            $verified = true;
        }

        if ($this->settings->get('pteranodon::approvals:enabled') != 'true') {
            $approved = true;
        }

        $this->creationService->handle([
            'email' => $request->input('email'),
            'username' => $request->input('user'),
            'name_first' => 'Pteranodon',
            'name_last' => 'User',
            'password' => $request->input('password'),
            'ip' => $request->getClientIp(),
            'store_cpu' => $this->settings->get($prefix . 'cpu', 0),
            'store_memory' => $this->settings->get($prefix . 'memory', 0),
            'store_disk' => $this->settings->get($prefix . 'disk', 0),
            'store_slots' => $this->settings->get($prefix . 'slot', 0),
            'store_ports' => $this->settings->get($prefix . 'port', 0),
            'store_backups' => $this->settings->get($prefix . 'backup', 0),
            'store_databases' => $this->settings->get($prefix . 'database', 0),
            'approved' => $approved,
            'verified' => $verified,
        ]);

        return new JsonResponse([
            'data' => [
                'complete' => true,
                'intended' => $this->redirectPath(),
            ],
        ]);
    }
}
