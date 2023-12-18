<?php

namespace Pteranodon\Console\Commands\Schedule;

use Pteranodon\Models\Server;
use Illuminate\Console\Command;
use Pteranodon\Services\Analytics\AnalyticsReviewService;

class AnalyticsReviewCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'p:schedule:analytics-review';

    /**
     * @var string
     */
    protected $description = 'Reviews server analytics and creates messages for servers.';

    /**
     * AnalyticsReviewCommand constructor.
     */
    public function __construct(private AnalyticsReviewService $reviewService)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     */
    public function handle()
    {
        foreach (Server::all() as $server) {
            $this->reviewService->handle($server);
        }
    }
}
