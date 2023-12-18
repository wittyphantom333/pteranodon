<?php

namespace Pteranodon\Http\Controllers\Admin\Pteranodon;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Pteranodon\Http\Controllers\Controller;
use Pteranodon\Extensions\Spatie\Fractalistic\Fractal;
use Pteranodon\Services\Helpers\SoftwareVersionService;
use Pteranodon\Repositories\Wings\DaemonServerRepository;
use Pteranodon\Traits\Controllers\PlainJavascriptInjection;
use Pteranodon\Contracts\Repository\NodeRepositoryInterface;
use Pteranodon\Contracts\Repository\ServerRepositoryInterface;

class IndexController extends Controller
{
    use PlainJavascriptInjection;

    public function __construct(
        private Fractal $fractal,
        private DaemonServerRepository $repository,
        private SoftwareVersionService $versionService,
        private NodeRepositoryInterface $nodeRepository,
        private ServerRepositoryInterface $serverRepository,
    ) {
    }

    public function index(): View
    {
        $nodes = $this->nodeRepository->all();
        $servers = $this->serverRepository->all();
        $allocations = DB::table('allocations')->count();
        $suspended = DB::table('servers')->where('status', 'suspended')->count();

        $memoryUsed = 0;
        $memoryTotal = 0;
        $diskUsed = 0;
        $diskTotal = 0;

        foreach ($nodes as $node) {
            $stats = $this->nodeRepository->getUsageStatsRaw($node);

            $memoryUsed += $stats['memory']['value'];
            $memoryTotal += $stats['memory']['max'];
            $diskUsed += $stats['disk']['value'];
            $diskTotal += $stats['disk']['max'];
        }

        $this->injectJavascript([
            'servers' => $servers,
            'diskUsed' => $diskUsed,
            'diskTotal' => $diskTotal,
            'suspended' => $suspended,
            'memoryUsed' => $memoryUsed,
            'memoryTotal' => $memoryTotal,
        ]);

        return view('admin.pteranodon.index', [
            'version' => $this->versionService,
            'servers' => $servers,
            'allocations' => $allocations,
            'used' => [
                'memory' => $memoryUsed,
                'disk' => $memoryTotal,
            ],
            'available' => [
                'memory' => $memoryTotal,
                'disk' => $diskTotal,
            ],
        ]);
    }

    /**
     * Handle settings update.
     *
     * @throws \Pteranodon\Exceptions\Model\DataValidationException
     * @throws \Pteranodon\Exceptions\Repository\RecordNotFoundException
     */
    public function update(BaseSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('pteranodon::' . $key, $value);
        }

        $this->alert->success('Pteranodon Settings have been updated.')->flash();

        return redirect()->route('admin.settings');
    }
}
