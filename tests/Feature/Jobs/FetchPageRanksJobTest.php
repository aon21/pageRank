<?php

use App\Jobs\FetchPageRanksJob;
use App\Models\Domain;
use App\Services\PageRankService;
use Database\Factories\DomainFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->now = now();

    $this->mock = mock(PageRankService::class);
    app()->instance(PageRankService::class, $this->mock);
});

function expectSaveRanks(Collection $ranks): void
{
    Domain::upsert($ranks->toArray(), ['domain'], ['rank', 'updated_at']);
}

it('fetches domains and inserts them into the database', function () {
    $this->mock->expects('getDomains')->andReturns(collect(['example.com']));

    $this->mock->expects('getPageRanks')->andReturns(collect([
        [
            'domain'     => 'example.com',
            'rank'       => 7.1,
            'created_at' => $this->now,
            'updated_at' => $this->now
        ],
    ]));

    $this->mock->expects('saveRanks')->andReturnUsing(fn(Collection $ranks) => expectSaveRanks($ranks));

    FetchPageRanksJob::dispatchSync();

    expect(Domain::where('domain', 'example.com')->exists())->toBeTrue();
});

it('updates existing domain rank', function () {
    DomainFactory::new()->create(['domain' => 'example.com', 'rank' => 1.0]);

    $this->mock->expects('getDomains')->andReturns(collect(['example.com']));

    $this->mock->expects('getPageRanks')->andReturns(collect([
        [
            'domain'     => 'example.com',
            'rank'       => 9.9,
            'created_at' => $this->now,
            'updated_at' => $this->now
        ],
    ]));

    $this->mock->expects('saveRanks')->andReturnUsing(fn(Collection $ranks) => expectSaveRanks($ranks));

    FetchPageRanksJob::dispatchSync();

    expect(Domain::where('domain', 'example.com')->first()->rank)->toBe(9.9);
});

it('does nothing when no domains are returned', function () {
    $this->mock->expects('getDomains')->andReturns(collect());
    $this->mock->allows('getPageRanks')->never();
    $this->mock->allows('saveRanks')->never();

    FetchPageRanksJob::dispatchSync();

    expect(Domain::count())->toBe(0);
});

it('skips saving if ranks are empty', function () {
    $this->mock->expects('getDomains')->andReturns(collect(['google.com']));
    $this->mock->expects('getPageRanks')->andReturns(collect());

    $this->mock->expects('saveRanks')->andReturnUsing(function ($ranks) {
        expect($ranks)->toBeEmpty();
    });

    FetchPageRanksJob::dispatchSync();
});

it('handles duplicate domains gracefully', function () {
    $this->mock->expects('getDomains')->andReturns(collect(['example.com', 'example.com']));

    $this->mock->expects('getPageRanks')->andReturns(collect([
        [
            'domain'     => 'example.com',
            'rank'       => 5,
            'created_at' => $this->now,
            'updated_at' => $this->now
        ],
    ]));

    $this->mock->expects('saveRanks')->andReturnUsing(function ($ranks) {
        expect($ranks->count())->toBe(1);
    });

    FetchPageRanksJob::dispatchSync();
});
