<?php

namespace Pteranodon\Http\Controllers\Admin;

use Ramsey\Uuid\Uuid;
use Illuminate\View\View;
use Pteranodon\Models\Nest;
use Pteranodon\Models\Mount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pteranodon\Models\Location;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pteranodon\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Pteranodon\Http\Requests\Admin\MountFormRequest;
use Pteranodon\Repositories\Eloquent\MountRepository;
use Pteranodon\Contracts\Repository\NestRepositoryInterface;
use Pteranodon\Contracts\Repository\LocationRepositoryInterface;

class MountController extends Controller
{
    /**
     * MountController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected NestRepositoryInterface $nestRepository,
        protected LocationRepositoryInterface $locationRepository,
        protected MountRepository $repository,
        protected ViewFactory $view
    ) {
    }

    /**
     * Return the mount overview page.
     */
    public function index(): View
    {
        return $this->view->make('admin.mounts.index', [
            'mounts' => $this->repository->getAllWithDetails(),
        ]);
    }

    /**
     * Return the mount view page.
     *
     * @throws \Pteranodon\Exceptions\Repository\RecordNotFoundException
     */
    public function view(string $id): View
    {
        $nests = Nest::query()->with('eggs')->get();
        $locations = Location::query()->with('nodes')->get();

        return $this->view->make('admin.mounts.view', [
            'mount' => $this->repository->getWithRelations($id),
            'nests' => $nests,
            'locations' => $locations,
        ]);
    }

    /**
     * Handle request to create new mount.
     *
     * @throws \Throwable
     */
    public function create(MountFormRequest $request): RedirectResponse
    {
        $model = (new Mount())->fill($request->validated());
        $model->forceFill(['uuid' => Uuid::uuid4()->toString()]);

        $model->saveOrFail();
        $mount = $model->fresh();

        $this->alert->success('Mount was created successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Handle request to update or delete location.
     *
     * @throws \Throwable
     */
    public function update(MountFormRequest $request, Mount $mount): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            return $this->delete($mount);
        }

        $mount->forceFill($request->validated())->save();

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Delete a location from the system.
     *
     * @throws \Exception
     */
    public function delete(Mount $mount): RedirectResponse
    {
        $mount->delete();

        return redirect()->route('admin.mounts');
    }

    /**
     * Adds eggs to the mount's many-to-many relation.
     */
    public function addEggs(Request $request, Mount $mount): RedirectResponse
    {
        $validatedData = $request->validate([
            'eggs' => 'required|exists:eggs,id',
        ]);

        $eggs = $validatedData['eggs'] ?? [];
        if (count($eggs) > 0) {
            $mount->eggs()->attach($eggs);
        }

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Adds nodes to the mount's many-to-many relation.
     */
    public function addNodes(Request $request, Mount $mount): RedirectResponse
    {
        $data = $request->validate(['nodes' => 'required|exists:nodes,id']);

        $nodes = $data['nodes'] ?? [];
        if (count($nodes) > 0) {
            $mount->nodes()->attach($nodes);
        }

        $this->alert->success('Mount was updated successfully.')->flash();

        return redirect()->route('admin.mounts.view', $mount->id);
    }

    /**
     * Deletes an egg from the mount's many-to-many relation.
     */
    public function deleteEgg(Mount $mount, int $egg_id): Response
    {
        $mount->eggs()->detach($egg_id);

        return response('', 204);
    }

    /**
     * Deletes a node from the mount's many-to-many relation.
     */
    public function deleteNode(Mount $mount, int $node_id): Response
    {
        $mount->nodes()->detach($node_id);

        return response('', 204);
    }
}
