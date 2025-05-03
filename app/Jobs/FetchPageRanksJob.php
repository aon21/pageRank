<?php

namespace App\Jobs;

use App\Services\PageRankService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class FetchPageRanksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        /** @var PageRankService $pageRankService */
        $pageRankService = app(PageRankService::class);

        $domains = $pageRankService->getDomains();

        if ($domains->isEmpty()) {
            return;
        }

        $pageRankService->saveRanks(
            $pageRankService->getPageRanks($domains)
        );
    }
}
