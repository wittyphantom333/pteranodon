<?php

namespace Pteranodon\Http\Controllers\Admin\Users;

use Illuminate\View\View;
use Pteranodon\Models\User;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pteranodon\Http\Controllers\Controller;
use Pteranodon\Services\Users\UserUpdateService;
use Pteranodon\Exceptions\Model\DataValidationException;
use Pteranodon\Exceptions\Repository\RecordNotFoundException;
use Pteranodon\Http\Requests\Admin\Users\ResourceFormRequest;

class ResourceController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct(private AlertsMessageBag $alert, private UserUpdateService $updateService)
    {
    }

    /**
     * Display user resource page.
     */
    public function view(User $user): View
    {
        return view('admin.users.resources', ['user' => $user]);
    }

    /**
     * Update a user's resource balances.
     *
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function update(ResourceFormRequest $request, User $user): RedirectResponse
    {
        $this->updateService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($user, $request->normalize());

        $this->alert->success('User resources have been updated.')->flash();

        return redirect()->route('admin.users.resources', $user->id);
    }
}
