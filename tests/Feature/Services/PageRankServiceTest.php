<?php


use App\DTO\DomainRankData;
use App\Models\Domain;
use App\Services\PageRankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('should fetch, parse, and store real domain data using service', function () {
    Http::fake([
        '*' => Http::response([
            'response' => [
                ['domain' => 'example.com', 'page_rank_integer' => 10]
            ]
        ])
    ]);

    app(PageRankService::class)->saveRanks(
        collect([new DomainRankData('example.com', 10)])
    );

    expect(Domain::where('domain', 'example.com')->first()->rank)->toBe(10);
});
